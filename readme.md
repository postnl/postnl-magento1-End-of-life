# PostNL Magento 1 Extensie

---

### Installatie 

Via de onderstaande link kunt u de installatiehandleiding van de PostNL Magento extensie vinden. Hierin wordt stap voor stap beschreven hoe u de extensie op uw omgeving kan installeren.


https://servicedesk.tig.nl/hc/nl/articles/206495958

---

### Gebruikershandleiding

Na installatie kunt u de PostNL Magento extensie naar wens configureren. De gebruikershandleiding beschrijft uitvoerig welke opties kunnen worden geconfigureerd. U kunt de handleiding vinden via de onderstaande link.

https://servicedesk.tig.nl/hc/nl/articles/206496008

---

### Aanvullende informatie

Release notes:

https://servicedesk.tig.nl/hc/nl/articles/206495908

Knowledge base & FAQ:

https://servicedesk.tig.nl/hc/nl/categories/200427077

---

### Installatie via Modman (voor gevorderde gebruikers).

Zorg er eerst voor dat de symlinks aanstaan in uw Magento installatie.
Ga naar System > Configuration > Advanced > Developer en schakel Allow Symlinks aan.

Login via ssh en ga naar de Root van uw Magento installatie 
en voer de onderstaande commando's uit.

Mocht de .modman map niet aanwezig zijn dan kunt u deze aanmaken met het onderstaande commando.
* modman init

Installatie PostNL Magento extensie via Modman. 
* cd .modman 
* modman clone git@github.com:tig-nl/tig-extension-tig-postnl.git
* cd tig-extension-tig-postnl
* modman deploy