# PostNL Magento 1 Extensie

---

## Installatie via Modman

Zorg er eerst voor dat de symlinks aanstaan in uw Magento installatie.
Ga naar System > Configuration > Advanced > Developer en schakel Allow Symlinks aan.

Login via ssh en ga naar de Root van uw Magento installatie.

Om Modman te installeren
$ modman init

Om de PostNL extensie te installeren via Modman.
$ cd .modman
$ modman clone git@github.com:tig-nl/tig-extension-tig-postnl.git
$ cd tig-extension-tig-postnl
$ modman deploy

---

## Installatie via SFTP

1. Log in op uw Magento beheeromgeving.

2. Controleer of de compiler uit staat via  Systeem >  Gereedschap >  Compilatie. Zet de compiler uit wanneer deze ingeschakeld staat.
Let op: Wanneer de compiler toch aan staat, dan kan de installatie mislukken.

3. Pak het tig_postnl-x.y.z_enterprise/community_edition.zip bestand uit die u bij stap 1 heeft gedownload. Pak vervolgens het tig_postnl-x.y.z_enterprise/community_edition.tgz bestand uit.

4. Login op uw SFTP server met uw favoriete SFTP-browser. Upload de mappen app, lib en skin.
Let op:  Er worden geen bestanden overschreven. De mappen dienen samengevoegd te worden. Ga pas verder als alle bestanden zijn geüpload.

5. Ga in de beheeromgeving van uw Magento webshop naar  Systeem >  Beheer cache. Klik vervolgens op  Selecteer alles, controleer of de  Acties dropdown op  Ververs staat en klik op de  Bevestig button.

6. Klik in de Magento beheeromgeving rechtsboven op  Uitloggen en log vervolgens opnieuw in.  Let op:  Dit is belangrijk om verder te kunnen gaan met het installatieproces.
 
7. Ga in de Magento backend naar  Systeem >  Configuratie en klik links in de navigatie onder het tabje  Verkoop op  PostNL.
De installatie is afgerond, de volgende stap is het configureren van de extensie.

---

## Installatie- & gebruikershandleiding

Installatiehandleiding
https://servicedesk.tig.nl/hc/nl/articles/206495958-PostNL-Magento-extensie-installatiehandleiding

Gebruikershandleiding
https://servicedesk.tig.nl/hc/nl/articles/206496008-PostNL-Magento-extensie-gebruikershandleiding

---

## Aanvullende informatie

Release notes:
https://servicedesk.tig.nl/hc/nl/articles/206495908-Release-notes-van-de-PostNL-Magento-extensie

Knowledge base & FAQ
https://servicedesk.tig.nl/hc/nl/categories/200427077-PostNL-Magento-1

