# GiftFlow API
**GiftFlow** is a fictional gift card platform.
Each card has a redemption code, and once the card is redeemed, the gift card issuer receives a notification via a webhook.

## Trade-Offs
- Utilizei o Laravel Sail, mas para evitar a instalação do Composer e PHP localmente, incorporei os arquivos do
  Docker no projeto (./docker). Dessa forma, é possível rodar o projeto utilizando apenas o Docker.
- Se precisar fazer um ajuste rápido e rodar os testes para garantir que tudo está ok, vc consegue fazer isso
sem levantar todo o ambiente Docker, mas pra isso vc precisa ter o PHP e o Composer instalados localmente.
- No recebimento do webhook, optei por não validar os inputs e usar jobs para processar as notificações de forma assíncrona.
  Dessa forma, o endpoint responde rapidamente, e o processamento das notificações é feito em segundo plano.

## Configuração Inicial
- Setar um valor na variável GIFTFLOW_WEBHOOK_SECRET;

## Execução do Projeto
````bash
./sail up -d

# Para processar as jobs:
./sail artisan queue:work
````

## Comandos Úteis
- vendor/bin/pint: Corrige a sintaxe do código seguindo os padrões do Laravel Pint.

## Dicas
- Antes de enviar requests para os endpoints, lembre de configurar os headers conforme abaixo:
``
  'Content-Type' => 'application/json',
  'Accept' => 'application/json',
``
