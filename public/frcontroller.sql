-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 13/07/2026 às 15:22
-- Versão do servidor: 8.0.44-0ubuntu0.24.04.2
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `frcontroller`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixa`
--

CREATE TABLE `caixa` (
  `IDCaixa` int NOT NULL,
  `IDFilial` int NOT NULL,
  `DTAberturaCaixa` datetime DEFAULT NULL,
  `DTFechamentoCaixa` datetime DEFAULT NULL,
  `STCaixa` int DEFAULT '0',
  `NMPdv` varchar(45) COLLATE utf8mb3_bin NOT NULL,
  `NMSenhaPDV` varchar(10) COLLATE utf8mb3_bin NOT NULL,
  `STDelete` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `IDCategoria` int NOT NULL,
  `DSCategoria` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `IDFilial` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `IDCliente` int NOT NULL,
  `NMCliente` varchar(250) COLLATE utf8mb3_bin NOT NULL,
  `NMEmailCliente` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `NUTelefoneCliente` varchar(11) COLLATE utf8mb3_bin NOT NULL,
  `NUCpfCliente` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `IDFilial` int NOT NULL,
  `DSEnderecoJSON` text COLLATE utf8mb3_bin,
  `STDelete` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `colaboradores`
--

CREATE TABLE `colaboradores` (
  `IDColaborador` int NOT NULL,
  `NMColaborador` varchar(100) COLLATE utf8mb3_bin NOT NULL,
  `NMEmailColaborador` varchar(100) COLLATE utf8mb3_bin NOT NULL,
  `NMCargoColaborador` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `NUCpfColaborador` varchar(11) COLLATE utf8mb3_bin NOT NULL,
  `VLSalario` float DEFAULT NULL,
  `DTAdmissao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `STFerias` int DEFAULT '0',
  `STAcesso` int DEFAULT '1',
  `IDComissao` int DEFAULT '0',
  `IDFilial` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comissionados`
--

CREATE TABLE `comissionados` (
  `IDComissionado` int NOT NULL,
  `IDComissao` int NOT NULL,
  `IDColaborador` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comissoes`
--

CREATE TABLE `comissoes` (
  `IDComissao` int NOT NULL,
  `NMComissao` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `NUPorcentagem` float NOT NULL,
  `IDFilial` int NOT NULL,
  `TPComissao` varchar(20) COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `IDLote` int NOT NULL,
  `IDProduto` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `QTCompra` int NOT NULL,
  `DTReposicao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contapagar`
--

CREATE TABLE `contapagar` (
  `IDConta` int NOT NULL,
  `IDFilial` int NOT NULL,
  `NMConta` varchar(45) COLLATE utf8mb3_bin NOT NULL,
  `DTExpedicaoConta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DTVencimentoConta` datetime NOT NULL,
  `STConta` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Pendente',
  `VLConta` float NOT NULL DEFAULT '0',
  `DSJustificativaConta` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contas`
--

CREATE TABLE `contas` (
  `IDContaPagar` int NOT NULL,
  `IDFilial` int NOT NULL,
  `NMConta` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `DTExpedicao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DTVencimento` datetime NOT NULL,
  `STConta` int DEFAULT '0',
  `VLConta` float NOT NULL DEFAULT '0',
  `DSJustificativaConta` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos`
--

CREATE TABLE `contratos` (
  `IDContrato` int NOT NULL,
  `IDPlano` int NOT NULL,
  `STContrato` int DEFAULT '1',
  `DSEndContrato` text COLLATE utf8mb3_bin NOT NULL,
  `NMContratante` varchar(100) COLLATE utf8mb3_bin DEFAULT NULL,
  `NMEmailContratante` varchar(100) COLLATE utf8mb3_bin DEFAULT NULL,
  `NUCpfContratante` varchar(18) COLLATE utf8mb3_bin DEFAULT NULL,
  `NUTelefoneContato` varchar(11) COLLATE utf8mb3_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `crediarios`
--

CREATE TABLE `crediarios` (
  `IDCrediario` int NOT NULL,
  `IDCliente` int NOT NULL,
  `NUCredito` float NOT NULL,
  `DTInicioCredito` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DTTerminoCredito` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cupons`
--

CREATE TABLE `cupons` (
  `IDCupom` int NOT NULL,
  `IDCaixa` int NOT NULL,
  `ANCupom` longtext COLLATE utf8mb3_bin NOT NULL,
  `CDVenda` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `IDCliente` int NOT NULL,
  `IDFilial` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `custosordem`
--

CREATE TABLE `custosordem` (
  `IDCusto` int NOT NULL,
  `IDProduto` int NOT NULL,
  `IDOrdem` int NOT NULL,
  `NUQuantidade` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `devedores`
--

CREATE TABLE `devedores` (
  `IDDevedor` int NOT NULL,
  `IDCliente` int NOT NULL,
  `VLDivida` float NOT NULL,
  `DTInicioDivida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `devolucoes`
--

CREATE TABLE `devolucoes` (
  `IDDevolucao` int NOT NULL,
  `IDCliente` int DEFAULT NULL,
  `IDProduto` int NOT NULL,
  `IDFilial` int DEFAULT NULL,
  `DSMotivo` varchar(250) COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `IDEmpresa` int NOT NULL,
  `IDContrato` varchar(45) COLLATE utf8mb3_bin NOT NULL,
  `NMFantasiaEmpresa` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `NMRazaoEmpresa` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `NUCnpjEmpresa` varchar(14) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `STEmpresa` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `filiais`
--

CREATE TABLE `filiais` (
  `IDFilial` int NOT NULL,
  `IDEmpresa` int NOT NULL,
  `DSEnderecoJSON` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `NMFilial` varchar(100) COLLATE utf8mb3_bin NOT NULL,
  `NUTelefoneFilial` varchar(16) COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `IDFornecedor` int NOT NULL,
  `IDFilial` int NOT NULL,
  `NMFornecedor` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `DSEmailFornecedor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `DSTelefoneFornecedor` varchar(18) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `DSEndFornecedor` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `STDelete` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2023_12_16_073649_create_movimentacoes_table', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` bigint UNSIGNED NOT NULL,
  `IDProduto` int NOT NULL,
  `TPMovimentacao` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VLMovimentacao` double(8,2) NOT NULL,
  `QTMovimentacao` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordemservico`
--

CREATE TABLE `ordemservico` (
  `IDOrdem` int NOT NULL,
  `IDServico` int NOT NULL,
  `IDCliente` int NOT NULL,
  `IDColaborador` int NOT NULL,
  `DTServico` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `STServico` int DEFAULT '0',
  `DSOrdemServico` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `DSServico` longtext COLLATE utf8mb3_bin,
  `DSNota` text COLLATE utf8mb3_bin,
  `DTSaida` datetime DEFAULT NULL,
  `IDPagamento` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `IDPagamento` int NOT NULL,
  `NMPagamento` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `QTDesconto` float DEFAULT NULL,
  `DSMetodo` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `QTParcelas` int DEFAULT NULL,
  `TPDesconto` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `IDFilial` int NOT NULL,
  `NUJuros` float DEFAULT '0',
  `STDelete` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos`
--

CREATE TABLE `planos` (
  `IDPlano` int NOT NULL,
  `NMPlano` varchar(100) COLLATE utf8mb3_bin NOT NULL,
  `DSPlano` text COLLATE utf8mb3_bin NOT NULL,
  `VLPlano` float NOT NULL,
  `TMPlano` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `IDProduto` int NOT NULL,
  `IDFornecedor` int NOT NULL,
  `IDCategoria` int NOT NULL,
  `NMProduto` varchar(250) COLLATE utf8mb3_bin NOT NULL,
  `NUEstoqueProduto` int NOT NULL,
  `NUEstoqueMinimo` int NOT NULL,
  `DSUnidadeProduto` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `DTEntradaProduto` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DTValidadeProduto` date DEFAULT NULL,
  `NUCustoProduto` float NOT NULL,
  `NUValorProduto` float NOT NULL,
  `DSImagemProduto` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `DSCodigoProduto` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `NULucroProduto` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `DSGarantiaProduto` text COLLATE utf8mb3_bin,
  `STInsumo` varchar(45) COLLATE utf8mb3_bin DEFAULT '0',
  `TPIdentificacao` varchar(2) COLLATE utf8mb3_bin DEFAULT '0',
  `STDelete` int DEFAULT NULL,
  `NUCustoTotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `promocionais`
--

CREATE TABLE `promocionais` (
  `IDPromocional` int NOT NULL,
  `IDPromocao` int NOT NULL,
  `IDProduto` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `promocoes`
--

CREATE TABLE `promocoes` (
  `IDPromocao` int NOT NULL,
  `NMPromo` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `DTInicioPromo` datetime NOT NULL,
  `DTTerminoPromo` datetime NOT NULL,
  `NUDescontoPromo` float NOT NULL,
  `TPDesconto` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `IDFilial` int NOT NULL,
  `STDelete` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `IDServico` int NOT NULL,
  `VLBase` float NOT NULL,
  `DSTipoServico` varchar(50) COLLATE utf8mb3_bin NOT NULL,
  `IDFilial` varchar(45) COLLATE utf8mb3_bin NOT NULL,
  `DSGarantiaServico` text COLLATE utf8mb3_bin NOT NULL,
  `STDelete` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@admin.com', '2026-07-13 18:03:24', '$2y$12$yJ8TM5u8UViWKtb3jaw3u.4obOcnh5Swyw8B/kDVoG/mjs.AiMqi2', NULL, '2026-07-13 18:03:24', '2026-07-13 18:03:24'),
(2, 'Mr. Jeffry Gusikowski', 'twaelchi@example.com', '2026-07-13 18:03:24', '$2y$12$eYsrofXLJ0ubzXMtXBRdXeqRqyW8SBGud5vQwpEy9/o6Uf69ibl7C', 'mZYSLjiOX0', '2026-07-13 18:03:25', '2026-07-13 18:03:25'),
(3, 'Ettie Rolfson', 'juwan.reichel@example.com', '2026-07-13 18:03:25', '$2y$12$eYsrofXLJ0ubzXMtXBRdXeqRqyW8SBGud5vQwpEy9/o6Uf69ibl7C', 'DCpDJasXvQ', '2026-07-13 18:03:25', '2026-07-13 18:03:25'),
(4, 'Zelma Okuneva', 'kelli.herman@example.com', '2026-07-13 18:03:25', '$2y$12$eYsrofXLJ0ubzXMtXBRdXeqRqyW8SBGud5vQwpEy9/o6Uf69ibl7C', 'HIPq43TuHB', '2026-07-13 18:03:25', '2026-07-13 18:03:25'),
(5, 'Trisha Dach', 'shad71@example.org', '2026-07-13 18:03:25', '$2y$12$eYsrofXLJ0ubzXMtXBRdXeqRqyW8SBGud5vQwpEy9/o6Uf69ibl7C', 'i4JDFxYw0U', '2026-07-13 18:03:25', '2026-07-13 18:03:25'),
(6, 'Efren Schmeler', 'zulauf.haylie@example.com', '2026-07-13 18:03:25', '$2y$12$eYsrofXLJ0ubzXMtXBRdXeqRqyW8SBGud5vQwpEy9/o6Uf69ibl7C', 'fBSEwOjlby', '2026-07-13 18:03:25', '2026-07-13 18:03:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `IDVenda` int NOT NULL,
  `IDProduto` int NOT NULL,
  `IDFornecedor` int NOT NULL,
  `IDPromocao` int DEFAULT '0',
  `IDCliente` int DEFAULT NULL,
  `IDColaborador` int NOT NULL,
  `NUUnidadesVendidas` float NOT NULL,
  `IDCaixa` int DEFAULT NULL,
  `IDFilial` int NOT NULL,
  `STVenda` int DEFAULT '1',
  `DTVenda` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IDPagamento` int DEFAULT '0',
  `VLVenda` float NOT NULL,
  `CDVenda` varchar(50) COLLATE utf8mb3_bin DEFAULT NULL,
  `IDOrdem` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `caixa`
--
ALTER TABLE `caixa`
  ADD PRIMARY KEY (`IDCaixa`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`IDCategoria`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`IDCliente`);

--
-- Índices de tabela `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD PRIMARY KEY (`IDColaborador`);

--
-- Índices de tabela `comissionados`
--
ALTER TABLE `comissionados`
  ADD PRIMARY KEY (`IDComissionado`);

--
-- Índices de tabela `comissoes`
--
ALTER TABLE `comissoes`
  ADD PRIMARY KEY (`IDComissao`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`IDLote`);

--
-- Índices de tabela `contapagar`
--
ALTER TABLE `contapagar`
  ADD PRIMARY KEY (`IDConta`);

--
-- Índices de tabela `contas`
--
ALTER TABLE `contas`
  ADD PRIMARY KEY (`IDContaPagar`);

--
-- Índices de tabela `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`IDContrato`);

--
-- Índices de tabela `crediarios`
--
ALTER TABLE `crediarios`
  ADD PRIMARY KEY (`IDCrediario`);

--
-- Índices de tabela `cupons`
--
ALTER TABLE `cupons`
  ADD PRIMARY KEY (`IDCupom`);

--
-- Índices de tabela `custosordem`
--
ALTER TABLE `custosordem`
  ADD PRIMARY KEY (`IDCusto`);

--
-- Índices de tabela `devedores`
--
ALTER TABLE `devedores`
  ADD PRIMARY KEY (`IDDevedor`);

--
-- Índices de tabela `devolucoes`
--
ALTER TABLE `devolucoes`
  ADD PRIMARY KEY (`IDDevolucao`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`IDEmpresa`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `filiais`
--
ALTER TABLE `filiais`
  ADD PRIMARY KEY (`IDFilial`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`IDFornecedor`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `ordemservico`
--
ALTER TABLE `ordemservico`
  ADD PRIMARY KEY (`IDOrdem`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`IDPagamento`);

--
-- Índices de tabela `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`IDPlano`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`IDProduto`);

--
-- Índices de tabela `promocionais`
--
ALTER TABLE `promocionais`
  ADD PRIMARY KEY (`IDPromocional`);

--
-- Índices de tabela `promocoes`
--
ALTER TABLE `promocoes`
  ADD PRIMARY KEY (`IDPromocao`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`IDServico`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`IDVenda`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `caixa`
--
ALTER TABLE `caixa`
  MODIFY `IDCaixa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `IDCategoria` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `IDCliente` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `colaboradores`
--
ALTER TABLE `colaboradores`
  MODIFY `IDColaborador` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comissionados`
--
ALTER TABLE `comissionados`
  MODIFY `IDComissionado` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comissoes`
--
ALTER TABLE `comissoes`
  MODIFY `IDComissao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `IDLote` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contapagar`
--
ALTER TABLE `contapagar`
  MODIFY `IDConta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contas`
--
ALTER TABLE `contas`
  MODIFY `IDContaPagar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `IDContrato` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `crediarios`
--
ALTER TABLE `crediarios`
  MODIFY `IDCrediario` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cupons`
--
ALTER TABLE `cupons`
  MODIFY `IDCupom` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `custosordem`
--
ALTER TABLE `custosordem`
  MODIFY `IDCusto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `devedores`
--
ALTER TABLE `devedores`
  MODIFY `IDDevedor` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `devolucoes`
--
ALTER TABLE `devolucoes`
  MODIFY `IDDevolucao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `IDEmpresa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `filiais`
--
ALTER TABLE `filiais`
  MODIFY `IDFilial` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `IDFornecedor` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ordemservico`
--
ALTER TABLE `ordemservico`
  MODIFY `IDOrdem` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `IDPagamento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `IDPlano` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `IDProduto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `promocionais`
--
ALTER TABLE `promocionais`
  MODIFY `IDPromocional` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `promocoes`
--
ALTER TABLE `promocoes`
  MODIFY `IDPromocao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `IDServico` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `IDVenda` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
