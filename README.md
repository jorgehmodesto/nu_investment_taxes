# capital-gains - Code challenge

#### Programa desenvolvida em PHP para execução de de aplicação através de linha de comando 

#### Decisões técnicas e arquiteturiais

* Uso de Symfony 5.4 como framework de desenvolvimento da aplicação, com base em linguagem PHP 7.4
    * Como PHP é uma linguagem de desenvolvimento mais voltada para WEB, frameworks como Symfony, tornam 
     recursos como `Command` mais legíveis e simples de serem usados e interpretados.
* Docker para provisionamento de ambiente
    * Um container com nginx
    * Um container para PHP-fpm

## Ambiente

Para subir o ambiente, precisamos ter instalados na máquina o `Docker` e `Docker Compose`. Segue um tutorial
de como realizar a instalação dos mesmos: 
* https://docs.docker.com/get-docker/
* https://docs.docker.com/compose/install/

Após instalados, iremos executar o seguinte comando para baixar as imagens e buildar os containers:
* `docker-compose up --build`

Após nosso ambiente estar rodando, iremos instalar as dependências do framework com o seguinte comando:
* `docker exec -it php-container composer install --no-interaction`
    * o parâmetro `--no-interaction` evita que quaisquer confirmações sejam solicitadas no processo de
    instalação das dependências.
    
## Aplicação

Para rodar a aplicação, devemos através do terminal, executar o seguinte comando:
* `docker exec -it php-container php bin/console capital-gains`

Ao executar o comando acima, será solicitado a entrada no formato JSON com as ordens, da seguinte forma:

> Please, provide the orders >

Então é só informar as ordens no formato solicitado (lembrando que precisa ser um formato válido de JSON
com uma estrutura parecida com o exemplo a seguir:

`[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
{"operation":"sell", "unit-cost":20.00, "quantity": 5000}]`

E então, a aplicação te dará um retorno com um resultado como este:

`[OK] - [{"tax":0}, {"tax":10000}]`