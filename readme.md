PostNL Magento 1 Extension

[![Build Status](https://travis-ci.org/tig-nl/postnl-magento1.svg?branch=master)](https://travis-ci.org/tig-nl/postnl-magento1)

### Installation

The link below redirects to the installation manual for the PostNL Magento extension. Within this manual we describe all the steps that are needed to install the extension in your environment.

https://confluence.tig.nl/display/TIGSD/English+PostNL+extension+installation+guide

### User manual

After installing you can configure the PostNL Magento extension to suit your needs. The user manual describes what options can be configured. 
The user manual can be found trough the following link: https://servicedesk.tig.nl/hc/nl/articles/206496008

### Additional information

Release notes:

https://servicedesk.tig.nl/hc/nl/articles/206495908

Knowledge base & FAQ:

https://servicedesk.tig.nl/hc/nl/categories/200427077

### Installation trough Modman (for advanced users)

Make sure that you have enabled symlinks in your Magento installation.
To enable this go to "System > Configuration > Advanced > Developer" and activate "Allow Symlinks".

Login trough SSH and go to the root of the Magento installation. Execute the following command:

````
cd .modman
git clone git@github.com:tig-nl/postnl-magento1.git
modman deploy postnl-magento1
````

### Submitting issues trough Github
## Please follow the guide below

- You will be asked some questions and requested to provide some information, please read them **carefully** and answer honestly
- Put an `x` into all the boxes [ ] relevant to your *issue* (like this: `[x]`)
- Use the *Preview* tab to see what your issue will actually look like

---

### Make sure you are using the *latest* version: https://tig.nl/postnl-magento-extensies/
Issues with outdated version will be rejected.
- [ ] I've **verified** and **I assure** that I'm running the latest version of the TIG PostNL Magento extension.

---

### What is the purpose of your *issue*?
- [ ] Bug report (encountered problems with the TIG PostNL Magento extension)
- [ ] Site support request (request for adding support for a new site)
- [ ] Feature request (request for a new functionality)
- [ ] Question
- [ ] Other

---

### Description of your *issue*, suggested solution and other information

Explanation of your *issue* in arbitrary form goes here. Please make sure the [description is worded well enough to be understood]. Provide as much context and examples as possible.
If work on your *issue* requires account credentials please provide them or explain how one can obtain them.


### TIG supportdesk

On Github we will respond in English even when the question was asked in Dutch.
