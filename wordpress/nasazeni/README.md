# Nástroje pro nasazení

## `upload.py`

Nahraje adresářový strom na webhosting přes FTPS.

```bash
python3 upload.py <lokální adresář> <vzdálený adresář>
python3 upload.py ./archiv-2026 /www/subdom/2026
```

Přihlašovací údaje čte ze souboru `ftp.txt` ve stejné složce:

```
server=66215.w15.wedos.net
user=w66215_xxx
pass=heslo
```

**Ten soubor nikdy necommituj.** Drž ho mimo repozitář, ve složce
`~/Projects/personal/bm-wordpress-local/staging/`.

### Proč vlastní skript a ne FileZilla

vsftpd na Wedosu vyžaduje, aby datové spojení navázalo na TLS session
řídicího spojení (`require_ssl_reuse`). Python `ftplib` to sám neumí
a vrací `522 SSL connection failed; session reuse required`. Řeší to
podtřída `FTPTLS` v tomhle skriptu.

Opakované spuštění je levné — soubory, které na serveru už jsou ve stejné
velikosti, přeskočí. Když se spojení rozpadne v půlce, prostě ho pusť znovu.

Celý postup nasazení i se seznamem pastí je v
[docs/wordpress/07-nasazeni.md](../../docs/wordpress/07-nasazeni.md).
