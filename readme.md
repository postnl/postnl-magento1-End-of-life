# PostNL Magento 1 Extension

The support and development for this extension has been discontinued.

### Installation trough Modman (for advanced users)

Make sure that you have enabled symlinks in your Magento installation.
To enable this go to "System > Configuration > Advanced > Developer" and activate "Allow Symlinks".

Login trough SSH and go to the root of the Magento installation. Execute the following command:

````
cd .modman
git clone git@github.com:postnl/postnl-magento1.git
modman deploy postnl-magento1
````
