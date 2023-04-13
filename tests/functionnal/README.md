# Functional Tests Beta 

Use playwright to check that the module works as expected with different configurations.
For now, it can only be run locally, but I which I run them with GitHub actions later 

Please create a user with email dev@dev.com / dev1234 to allow to test thing with logged customers

To run locally

> nvm use 14
> npm install
> npx playwright test --workers=1

As the tests change the configuration it's necessary to run them one by one