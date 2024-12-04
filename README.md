# Yivi authentication for SimpleSAMLphp
## or, How To Reinvent the Wheel Once Again

## ABANDONED
This repository is abandoned in a non-working state and archived just in case someone finds it useful as a starting point to develop an Yivi plugin for SimpleSAMLphp.

## Docker configuration
The following is how to create a Docker container for *testing*. It can be used as a base for a production environment too, but it may require some additional work, refer to SimpleSAMLphp'sn documentation.  
When in doubt about the details of the following instrucions, refer to SimpleSAMLphp's documantation.

### Keys and certificate
#### IdP
Put your private key and certificate inside the resources/cert directory. If you're just testing0, you can generate a private key and a self-signed certificate as follows:

`openssl req -newkey rsa:3072 -new -x509 -days 3652 -nodes -out example.org.crt -keyout example.org.pem`

If upon accessing SimpleSAMLphp admin interface you get an error about loading the private key, it is likely a permissions issue. To test if this is the case, try executing `chmod +r your_private_key_file` and restart the container. If it now works, figure out what the correct permissions settings are. The previous command makes the private kay readable by anyone, so it isn't suitable for a production environment

#### JWT signing keys
For test purposes, it is possible to reuse the previously generated private key also for signing JWT requests. For a prodction environment, it is recommended to generate 
a separate keypair for signing JWT requests.
