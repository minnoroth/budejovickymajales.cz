#!/usr/bin/env python3
"""Upload a directory tree over FTPS to WEDOS webhosting.

vsftpd there runs with require_ssl_reuse, so the data connection has to resume
the TLS session of the control connection. ftplib does not do that on its own,
hence the FTPTLS subclass below.

Usage: upload.py <local dir> <remote dir>
Re-running is cheap: files already present with the same size are skipped.
"""

import ftplib
import os
import ssl
import sys

CFG_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "ftp.txt")


class _ReusedSocket(ssl.SSLSocket):
    """Keeps the shared TLS session alive when the data channel closes."""

    def unwrap(self):
        pass


class FTPTLS(ftplib.FTP_TLS):
    def ntransfercmd(self, cmd, rest=None):
        conn, size = ftplib.FTP.ntransfercmd(self, cmd, rest)
        if self._prot_p:
            conn = self.context.wrap_socket(
                conn, server_hostname=self.host, session=self.sock.session)
            conn.__class__ = _ReusedSocket
        return conn, size


def load_cfg():
    cfg = {}
    with open(CFG_PATH, encoding="utf-8") as fh:
        for line in fh:
            line = line.strip()
            if "=" in line and not line.startswith("#"):
                k, v = line.split("=", 1)
                cfg[k.strip()] = v.strip()
    return cfg


def connect(cfg):
    f = FTPTLS(timeout=60)
    f.encoding = "utf-8"
    f.connect(cfg["server"], 21)
    f.login(cfg["user"], cfg["pass"])
    f.prot_p()
    f.set_pasv(True)
    return f


def ensure_dir(f, path):
    cur = ""
    for part in path.strip("/").split("/"):
        cur += "/" + part
        try:
            f.mkd(cur)
        except ftplib.error_perm as e:
            if not str(e).startswith("550"):
                raise


def remote_sizes(f, path):
    """Existing file sizes under path, so a re-run can skip what is done."""
    out = {}
    try:
        for name, facts in f.mlsd(path, ["type", "size"]):
            if facts.get("type") == "file":
                out[name] = int(facts.get("size", -1))
    except Exception:
        pass
    return out


def main():
    local, remote = sys.argv[1], sys.argv[2]
    cfg = load_cfg()
    f = connect(cfg)
    print(f"připojeno, nahrávám do {remote}", flush=True)
    ensure_dir(f, remote)

    files = []
    for root, _, names in os.walk(local):
        for n in names:
            full = os.path.join(root, n)
            rel = os.path.relpath(full, local).replace(os.sep, "/")
            files.append((full, rel))
    files.sort(key=lambda x: x[1])
    total = len(files)
    print(f"souborů k nahrání: {total}", flush=True)

    done = skipped = failed = 0
    made = set()
    cache = {}
    for i, (full, rel) in enumerate(files, 1):
        rdir = remote + "/" + os.path.dirname(rel) if os.path.dirname(rel) else remote
        if rdir not in made:
            ensure_dir(f, rdir)
            made.add(rdir)
            cache[rdir] = remote_sizes(f, rdir)
        base = os.path.basename(rel)
        local_size = os.path.getsize(full)
        if cache.get(rdir, {}).get(base) == local_size:
            skipped += 1
            continue
        try:
            with open(full, "rb") as fh:
                f.storbinary(f"STOR {rdir}/{base}", fh, blocksize=131072)
            done += 1
        except Exception as e:
            failed += 1
            print(f"  CHYBA {rel}: {str(e)[:70]}", flush=True)
            try:
                f.quit()
            except Exception:
                pass
            f = connect(cfg)
        if i % 25 == 0 or i == total:
            print(f"  {i}/{total} (nahráno {done}, přeskočeno {skipped}, chyb {failed})",
                  flush=True)

    try:
        f.quit()
    except Exception:
        pass
    print(f"\nHOTOVO: nahráno {done}, přeskočeno {skipped}, chyb {failed}", flush=True)


if __name__ == "__main__":
    main()
