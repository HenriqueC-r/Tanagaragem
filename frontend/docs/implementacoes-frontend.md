# Implementações do frontend

Este documento registra as alterações feitas nesta etapa do Tá na Garagem e explica onde cada comportamento está implementado. O frontend usa Vue 3, Quasar e Vue Router.

## Executar o frontend

Os comandos do projeto frontend devem ser executados dentro da pasta `frontend/`, onde está o `package.json`:

```bash
cd frontend
npm install
npm run dev
```

Executar `npm run dev` na raiz do repositório não inicia o frontend, pois não há um script `dev` configurado no `package.json` da raiz.

Comandos úteis, também dentro de `frontend/`:

```bash
npm run lint:check
npm run build
```

## Rotas

As rotas da aplicação ficam em [`src/router/routes.js`](../src/router/routes.js). O router está configurado para usar hash na URL; por exemplo, a página de detalhes aparece como `/#/veiculos/10`.

| Caminho         | Página                  | Observação                                                                                 |
| --------------- | ----------------------- | ------------------------------------------------------------------------------------------ |
| `/`             | `IndexPage.vue`         | Página inicial.                                                                            |
| `/veiculos`     | `VehiclesPage.vue`      | Busca e listagem de veículos.                                                              |
| `/veiculos/:id` | `VehicleDetailPage.vue` | Detalhes do veículo. A rota explícita evita que links para um veículo caiam na página 404. |
| `/entrar`       | `LoginPage.vue`         | Login integrado com a API.                                                                 |
| `/cadastrar`    | `RegisterPage.vue`      | Cadastro integrado para pessoa física ou loja.                                             |
| `/anunciar`     | `ComingSoonPage.vue`    | Placeholder informando que o cadastro de anúncio estará disponível em breve.               |
| demais caminhos | `ErrorNotFound.vue`     | Página 404.                                                                                |

`VehicleCard.vue` navega para `/veiculos/{id}` ao ser selecionado. A tela `/anunciar` continua informativa; o fluxo de criação de anúncios ainda não foi implementado no frontend.

## Login e sessão

A tela de login em [`src/pages/LoginPage.vue`](../src/pages/LoginPage.vue) envia e-mail e senha para `POST /api/login`. Ela valida os campos, exibe erros de credenciais ou de conexão e oferece um controle para revelar a senha.

- O token retornado pela API é guardado em `sessionStorage`, permanecendo disponível durante a sessão do navegador.
- A autenticação fica centralizada em `src/stores/auth.js`.
- Ao abrir a aplicação com um token salvo, o frontend valida a sessão usando `GET /api/me`.
- O menu da conta permite encerrar a sessão por `POST /api/logout`; o token local é removido mesmo que o servidor esteja indisponível.
- O cliente HTTP envia tokens como `Authorization: Bearer <token>`.

## Cadastro

A tela [`src/pages/RegisterPage.vue`](../src/pages/RegisterPage.vue) integra com `POST /api/register`. O formulário oferece cadastro de pessoa física ou loja e segue os campos obrigatórios do backend, incluindo CPF, telefone, cidade, estado e confirmação de senha; para lojas, também solicita razão social e CNPJ.

O telefone recebe máscara automática para fixo e celular e é enviado formatado. A cidade é escolhida de uma lista do IBGE carregada após a seleção do estado, evitando combinações digitadas livremente; se o serviço estiver indisponível, a tela informa e permite tentar de novo. CPF e CNPJ são enviados apenas com dígitos, como esperado pela API. A resposta de validação é exibida para o usuário. Após o cadastro, a tela informa o resultado e encaminha para o login; o endpoint não cria uma sessão nem retorna token automaticamente. Cadastro de loja pode ficar pendente de análise do CNPJ.

## Página inicial e banner

O banner principal está em [`src/pages/IndexPage.vue`](../src/pages/IndexPage.vue), com a imagem [`src/assets/corolla-hero.png`](../src/assets/corolla-hero.png).

- Em telas grandes, o banner mantém o texto e a busca à esquerda e reserva uma coluna à direita para um anúncio futuro. A coluna é apenas espaço reservado, sem moldura ou conteúdo de anúncio.
- Em telas pequenas, a coluna reservada é ocultada. O enquadramento do Corolla é ajustado para mostrar mais da parte dianteira, e o espaçamento entre texto e busca evita que os campos cubram o carro.
- Os estilos responsivos do banner usam breakpoints em `760px` e `480px`.
- A página também reúne destaques e categorias populares. A busca e a seleção de categoria encaminham para `/veiculos` com os parâmetros correspondentes.

O anúncio mencionado acima não está integrado; o espaço existe apenas para acomodar uma integração futura.

## Cards de veículos

O componente compartilhado [`src/components/VehicleCard.vue`](../src/components/VehicleCard.vue) é usado para apresentar veículos.

- Em telas acima de `600px`, mantém a apresentação vertical.
- Até `600px`, passa a uma disposição horizontal: foto à esquerda e informações à direita.
- O card continua navegável por clique e pela tecla Enter; quando não há foto, apresenta um ícone de imagem.
- Os dados exibidos incluem título, localização, ano, quilometragem, preço e, quando aplicável, um selo de destaque ou característica.

## Categorias populares

A grade de categorias também fica na página inicial:

- Em telas grandes, usa cinco colunas.
- Até `760px`, usa três colunas.
- Até `480px`, usa duas colunas; se houver um número ímpar de categorias, o último cartão fica centralizado na linha.
- Os cartões têm dimensões e alinhamento consistentes e uma interação visual discreta ao passar o cursor.

## Integração com a API e escopo

A página inicial carrega os destaques e as categorias pelos métodos `getFeatured` e `getCategories`, importados de `src/api`. Essas chamadas usam a API já existente; esta etapa não alterou os endpoints de gerenciamento de veículos do backend, incluindo `VehicleController`.

## Verificações

Durante os ajustes, foram feitas verificações visuais no navegador em tamanhos de tela móveis e desktop. O build do frontend passou após os ajustes responsivos anteriores; as alterações finais na grade de categorias também passaram nas verificações de Prettier e ESLint.
