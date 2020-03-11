Vault web-tools
===============

This is a small tool collection to manage some Vault tasks via a web interface.
This includes:

* Unseal operation management
* Use wrapping tokens
* View and rotate (and export) CRL of a PKI secret engine

## Setup
One way to use this tool collection is to deploy it via a 'php:apache' docker container.
Just mount this directory to `/var/www/html` inside the container. The tools are configured with environment variables:

* VAULT_ADDR  
	url of the vault instance including transport protocol and port (eg.: http://vault:8200)
* PKI_PATH  
	path of the PKI secret engine within vault
* CRL_EXPORT_PATH  
	absolute file path where to write the exported CRL

## Usage
### Manage Unseal Operation (unseal-manage.php)
-

### Wrapping (wrapping.php)
Lookup and unwrap a wrapping token. Wrapping token can be inputted via GET parameter 'token' (eg. `?token=s.ZN63Af4ARb4JSg0CQLghfEav`).

### Manage CRL (crl-manage.php)
-

### Example docker-compose.yml

```yml
version: '2.4'
services:
  authorityx-vault-web-tools:
    image: php:7.2-apache
    volumes:
      - ./web-tools:/var/www/html:ro
      - ./data/crl:/data/crl
    environment:
      - VAULT_ADDR=http://vault:8200
      - PKI_PATH=CA
      - CRL_EXPORT_PATH=/data/crl
```
