# GiftFlow API
**GiftFlow** is a fictional gift card platform.
Each card has a redemption code, and once the card is redeemed, the gift card issuer receives a notification via a webhook.


## Trade-Offs
- Utilizei o Laravel Sail, mas para evitar a instalação do Composer e PHP localmente, incorporei os arquivos do
  Docker no projeto (./docker). Dessa forma, é possível rodar o projeto utilizando apenas o Docker.

## Execução do Projeto
````bash
./sail up -d
````

## Comandos Úteis
- vendor/bin/pint: Corrige a sintaxe do código seguindo os padrões do Laravel Pint.

## Dicas
- Antes de enviar a request para a api, lembre de adicionar o header `Accept: application/json` para garantir que as 
respostas sejam no formato JSON.
