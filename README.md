# Manual de Uso: Módulo PreSales Intelipost

[![logo](https://image.prntscr.com/image/E8AfiBL7RQKKVychm7Aubw.png)](http://www.intelipost.com.br)

## Introdução

O módulo PreSales é uma extensão do módulo Intelipost Quote que acrescenta a funcionalidade de levar em consideração as datas de pré venda para cálculo do frete.
A consulta do frete é feita na [API Intelipost](https://docs.intelipost.com.br/v1/cotacao/criar-cotacao-por-produto).

Este manual foi divido em três partes:

  - [Instalação](#instalação): Onde você econtrará instruções para instalar nosso módulo.
  - [Configurações](#configurações): Onde você encontrará o caminho para realizar as configurações e explicações de cada uma delas.
  - [Uso](#uso): Onde você encontrará a maneira de utilização de cada uma das funcionalidades.
  
## Instalação
> É recomendado que você tenha um ambiente de testes para validar alterações e atualizações antes de atualizar sua loja em produção.

> A instalação do módulo é feita utilizando o Composer. Para baixar e instalar o Composer no seu ambiente acesse https://getcomposer.org/download/ e caso tenha dúvidas de como utilizá-lo consulte a [documentação oficial do Composer](https://getcomposer.org/doc/).

Navegue até o diretório raíz da sua instalação do Magento 2 e execute os seguintes comandos:


```
bin/composer require intelipost/magento2-presales  // Faz a requisição do módulo da Intelipost
bin/magento module:enable Intelipost_PreSales      // Ativa o módulo
bin/magento setup:upgrade                          // Registra a extensão
bin/magento setup:di:compile                       // Recompila o projeto Magento
```

## Configurações
Conforme comentado na introdução, o módulo PreSales é uma extensão do Quote. Portanto, é necessário que este último esteja configurado corretamente no seu ambiente.
Caso tenha alguma dúvida sobre a configuração do módulo Quote Intelipost, consulte [nosso manual](https://github.com/intelipost/magento2-quote).

Para acessar o menu de configurações, basta seguir os seguintes passos:

No menu à esquerda, acessar **Stores** -> **Configuration** -> **Intelipost** -> **Shipping Methods** -> **Intelipost - Pré Venda**:

![ps0](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/presales1.gif)


### Intelipost - Pré Venda

- **Ativado**: Se o módulo está ativo e deve ser apresentado no front da loja.
- **Nome**: Nome que ficará registrado no pedido no Magento.
- **Título**: Nome que será exibido no checkout ao lado de cada método da Intelipost.
![ps1](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/quote1.png)
------------

- **Título customizado para métodos de entrega**: Determinar como os métodos de envio serão exibidos para o cliente final. O primeiro %s será substuído pela descrição do método (exemplo: Expresso). O segundo %s será substituído pelo prazo ou data de entrega (exemplo: 3 dias).
- **Título customizado para entrega no mesmo dia**: Caso algum dos métodos de envio possua entrega com o prazo menor do que 24 horas, o seu título pode ser customizado aqui. (exemplo: "Entrega ainda hoje!!")
![ps2](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/presales1.png)
------------

- **Atributo para Pré-Venda**: Selecionar o atributo do produto que indica se é Pré Venda ou não.
- **Atributo para Encomenda**: Selecionar o atributo do produto que conterá os dias de Cross Docking.
- **Atributo para Pronta-Entrega**: Selecionar o atributo do produto que indicará se está disponível em estoque ou não.
- **Formato da Data**: Formato da data que será apresentada no momento do cálculo de frete.

![ps3](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/presales2.png)

------------

- **Entrega aplicável para países**: Países que a cotação deve abrangir.
- **Ordenação**: Caso exista algum outro método de envio ativo, essa configuração possibilita escolher em qual ordem o módulo de frete da Intelipost deve se posicionar após a cotação.

![ps4](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/presales3.png)


## Uso

Uma vez instalado e configurado, basta definir as datas de pré-venda dos produtos desejados direto no catálogo do Magento.

Para isso, siga o seguinte caminho:

No menu à esquerda, acessar **Catálogo** -> **Produtos** -> **Selecionar o Produto** -> **Rolar até as configurações de Pré Venda**:


![gif](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/presales5.gif)

- **Intelipost Product PreSales**: Deve ser configurado a data em que o produto estará disponível para despacho.
- **Intelipost Product Package**: Caso seja necessário adicionar algum dia de cross docking após esta data, o acréscimo em dias úteis deve ser feito neste campo.
- **Intelipost Product Ready To Go**: Configuração que indica se o produto estará disponível para despacho ou se é um produto de pré venda.
- **PreSales**: Configurar a data em que o produto estará disponível para pré venda.

![ps5](https://s3.amazonaws.com/email-assets.intelipost.net/integracoes/presales4.png)
