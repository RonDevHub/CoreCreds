<h1 align="center">
  <img src="https://commitcloud.net/repo-avatars/4eea19fff4dd54461fd32fb3e503e953af5780180414e629bb2213d26f2c11a0" height="85"><br>
  CoreCreds
</h1>

<div align="center">

![Created](https://mini-badges.rondev.de/forgejo/RonDevHub/CoreCreds/created-at/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/forgejo/RonDevHub/CoreCreds/lastcommit/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/stars/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/issues/*/*/en) ![GitHub Repo language](https://mini-badges.rondev.de/forgejo/RonDevHub/CoreCreds/language/*/*/en) ![GitHub Repo license](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/license/*/*/en) ![GitHub Repo release](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/release/*/*/en) ![GitHub Repo release](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/forks/*/*/en) ![GitHub Repo downlods](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/downloads/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/CoreCreds/watchers) [![status-badge](https://ci.commitcloud.net/api/badges/13/status.svg)](https://ci.commitcloud.net/repos/14) 

[![Buy me a coffee](https://mini-badges.rondev.de/icon/cuptogo/Buy_me_a_Coffee-c1d82f-222/for-the-badge "Buy me a coffee")](https://www.buymeacoffee.com/RonDev)
[![Buy me a coffee](https://mini-badges.rondev.de/icon/cuptogo/ko--fi.com-c1d82f-222/for-the-badge "Buy me a coffee")](https://ko-fi.com/U6U31EV2VS)
[![Pizza Power](https://mini-badges.rondev.de/icon/paypal/PayPal/for-the-badge "Pizza Power")](https://www.paypal.com/donate/?hosted_button_id=PWY939TPCQ3RA)
</div>
<hr>

CoreCreds ist ein extrem leichtgewichtiger, datenschutzfreundlicher und hochsicherer Generator für Passwörter, Passphrasen und Benutzernamen. Entwickelt ohne schwere Frameworks, ist die Anwendung maximal ressourcensparend und perfekt für den Betrieb im Homelab oder auf Standard-Webhostern (wie all-inkl.com) ausgelegt.

## Features

- **Sichere Passwörter:** Vollständig konfigurierbar (8-64 Zeichen) unter Verwendung eines echten CSPRNG (`random_int`).
- **Ressourceneffiziente Passphrasen:** Verwendet Dateistreaming (`fseek`), um Wortlisten direkt auf der Festplatte zu lesen. Der RAM-Verbrauch bleibt bei nahezu 0 MB. Unterdrückt doppelte Zeichen-Zuweisungen bei Wörtern.
- **Intelligente Benutzernamen:** Generierung basierend auf Wortlisten oder optionalen Basisnamen mit flexibler Ziffernplatzierung.
- **Datenschutz & Anti-Schulterblick:** Ein Inaktivitäts-Timer löscht alle Zustände im Browser nach 2 Minuten und erzwingt einen cache-freien Reload der Seite.
- **Modernes UI:** Gebaut mit Tailwind CSS und Alpine.js. Vollautomatische Erkennung des Hell/Dunkel-Modus sowie automatische Sprachregelung (DE/EN).

## Installation & Deployment

### Betrieb über Docker (Empfohlen)

Das Repository verfügt über einen integrierten GitHub-Worker, der fertige Images nach `ghcr.io/rondevhub/corecreds:latest` schiebt.

1. Erstelle eine `docker-compose.yml` wie im Repository angegeben.
2. Platziere deine Wortlisten (`dice-de.txt`, `dice-lat.txt`, `eff.txt`) im Ordner `./data/`.
3. Starte den Container:
   ```bash
   docker compose up -d
   ```

### Betrieb auf klassischem Webspace (z.B. all-inkl.com)

1. Lade den gesamten Inhalt des Projekts in dein Web-Verzeichnis hoch.
2. Setze das Wurzelverzeichnis deiner Domain oder Subdomain direkt auf den Ordner `public/`.
3. Platziere deine Wortlisten in den Ordner `data/`. Die mitgelieferte `.htaccess` sorgt automatisch für schöne URLs.

## Wortlisten
Gefunden auf <a href="https://theworld.com/~reinhold/diceware.html" target="_blank">theworld.com/~reinhold/diceware.html</a>

## Lizenz

Freie Software – Open Source.