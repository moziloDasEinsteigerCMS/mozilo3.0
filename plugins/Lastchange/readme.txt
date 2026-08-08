Lastchange Plugin für moziloCMS
===============================

Version: 3.2.2
Autor: mozilo (refactored)
PHP: ab Version 8.0 (getestet bis 8.4)


BESCHREIBUNG
------------
Das Plugin „Lastchange“ zeigt die zuletzt geänderte Inhaltsseite
samt Änderungsdatum an. Optional kann zusätzlich die Uhrzeit
ausgegeben werden.


VERWENDUNG IM TEMPLATE / INHALT
-------------------------------
Folgende Platzhalter stehen zur Verfügung:

{Lastchange}
Gibt Text, Seitenname (verlinkt) und Datum aus.

{Lastchange|text}
Gibt nur den Text „Letzte Änderung“ aus. Text im Admin indiv. festlegbar.

{Lastchange|page}
Gibt den Namen der zuletzt geänderten Inhaltsseite aus.

{Lastchange|pagelink}
Gibt den Namen der zuletzt geänderten Inhaltsseite als Link aus.

{Lastchange|date}
Gibt nur das Datum der letzten Änderung aus. Mit Option Uhrzeit (s. Checkbox im Admin)


KONFIGURATION IM ADMIN-BEREICH
------------------------------
Das Plugin kann im Admin-Bereich konfiguriert werden.

Einstellungen:

1. Eigener Text für „Letzte Änderung“
   Optionaler Text, der statt des Standardtextes verwendet wird.

2. Eigenes Datumsformat
   Das Datumsformat richtet sich nach der PHP-Funktion date().

   Beispiele:
   d.m.Y        → 23.10.2020
   d.m.Y H:i    → 23.10.2020 09:07
   d.m.Y H:i:s  → 23.10.2020 09:07:20
   Y-m-d        → 2020-10-23
   d. F Y       → 23. Oktober 2020

3. Uhrzeit anzeigen
   Wenn aktiviert, werden Zeitangaben (Stunden/Minuten/Sekunden)
   ausgegeben. Wenn deaktiviert, wird nur das Datum angezeigt,
   auch wenn das Datumsformat Zeitanteile enthält.

4. Versteckte Inhaltsseiten mit einbeziehen
   Wenn aktiviert, werden auch versteckte Seiten bei der
   Ermittlung der letzten Änderung berücksichtigt.


HINWEISE
--------
- Das Plugin verwendet intern das HTML-Element <time datetime="...">.
- Das Datumsformat wird validiert; ungültige Formate werden
  automatisch durch ein Standardformat ersetzt.
- Das Plugin arbeitet defensiv und erzeugt keine Fehlermeldungen,
  auch bei leeren oder neuen Installationen.


INSTALLATION
------------
1. LastChange.zip über Admin installieren
2. Plugin im Admin-Bereich aktivieren
3. Optional Konfiguration anpassen
4. Platzhalter im Template oder in Inhalten verwenden

