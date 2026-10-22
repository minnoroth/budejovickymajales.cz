#!/usr/bin/env python3
"""Static mirror of budejovickymajales.cz for the 2026 archive subdomain.

Saves a directory tree that can be uploaded to the root of
2026.budejovickymajales.cz and served by Apache with no PHP at all.

Pages without an extension are stored as <path>/index.html so DirectoryIndex
serves them. Absolute links to the live host are rewritten to root-relative
ones; everything else is left untouched.
"""

import os
import re
import sys
import time
import gzip
import urllib.request
import urllib.parse
from collections import deque

HOSTS = {"budejovickymajales.cz", "www.budejovickymajales.cz"}
START = "https://budejovickymajales.cz/"
OUT = sys.argv[1] if len(sys.argv) > 1 else "./mirror"
DELAY = 0.25          # be gentle with their server
SKIP = re.compile(r"^/(admin|wp-admin)(/|$)", re.I)
UA = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 "\
     "(KHTML, like Gecko) Chrome/140.0 Safari/537.36"

ASSET_EXT = re.compile(
    r"\.(css|js|png|jpe?g|gif|svg|webp|avif|ico|woff2?|ttf|eot|mp4|webm|pdf|zip)$", re.I)

seen, queue, saved, failed = set(), deque([START]), [], []


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": UA,
                                               "Accept-Encoding": "gzip"})
    with urllib.request.urlopen(req, timeout=25) as r:
        data = r.read()
        if r.headers.get("Content-Encoding") == "gzip":
            data = gzip.decompress(data)
        return data, r.headers.get_content_type(), r.geturl()


def local_path(path):
    """Map a URL path to a file on disk."""
    path = urllib.parse.unquote(path)
    if path.endswith("/"):
        path += "index.html"
    elif not ASSET_EXT.search(path) and not path.endswith(".html"):
        path = path.rstrip("/") + "/index.html"
    return os.path.join(OUT, path.lstrip("/"))


def rewrite(text):
    """Absolute links to the live host become root-relative."""
    text = re.sub(r"https?://(?:www\.)?budejovickymajales\.cz(?=[/\"'\s)])", "", text)
    text = re.sub(r"https?://(?:www\.)?budejovickymajales\.cz(?=[\"'\s)])", "/", text)
    return text


def extract(text, base):
    """Pull every same-host URL worth following out of a document."""
    found = set()
    pats = [r'(?:href|src|data-src)\s*=\s*["\']([^"\']+)["\']',
            r'url\(\s*["\']?([^"\')]+)["\']?\s*\)',
            r'background-image:\s*url\(\s*\\?["\']?([^"\')\\]+)']
    for p in pats:
        for m in re.findall(p, text, re.I):
            m = m.strip().replace("\\/", "/")
            if not m or m.startswith(("mailto:", "tel:", "javascript:", "data:", "#")):
                continue
            absu = urllib.parse.urljoin(base, m)
            pr = urllib.parse.urlparse(absu)
            if pr.scheme in ("http", "https") and pr.hostname in HOSTS:
                if not SKIP.match(pr.path):
                    found.add(urllib.parse.urlunparse(
                        (pr.scheme, pr.netloc, pr.path, "", pr.query, "")))
    return found


print(f"mirror → {OUT}", flush=True)
while queue:
    url = queue.popleft()
    key = url.split("#")[0]
    if key in seen:
        continue
    seen.add(key)
    try:
        body, ctype, final = fetch(key)
    except Exception as e:
        failed.append((key, str(e)[:60]))
        continue

    pr = urllib.parse.urlparse(key)
    # Assets keep their plain path: a static server ignores the ?v= cache buster,
    # so the file must land at the name the CSS actually asks for.
    if ASSET_EXT.search(pr.path) or not pr.query:
        path = pr.path
    else:
        path = pr.path + "_" + re.sub(r"\W+", "_", pr.query)
    dest = local_path(path)
    os.makedirs(os.path.dirname(dest), exist_ok=True)

    if ctype in ("text/html", "text/css", "application/javascript", "text/javascript"):
        text = body.decode("utf-8", "replace")
        for u in extract(text, final):
            if u.split("#")[0] not in seen:
                queue.append(u)
        with open(dest, "w", encoding="utf-8") as f:
            f.write(rewrite(text))
    else:
        with open(dest, "wb") as f:
            f.write(body)

    saved.append(dest)
    if len(saved) % 25 == 0:
        print(f"  {len(saved)} souborů, ve frontě {len(queue)}", flush=True)
    time.sleep(DELAY)

print(f"\nHOTOVO: {len(saved)} souborů, {len(failed)} chyb", flush=True)
for u, e in failed[:15]:
    print(f"  CHYBA {u} — {e}", flush=True)
