# 🔍 Relatório de Auditoria de Segurança - MangaVerse

**Data**: 6 de maio de 2026  
**Projeto**: MangaVerse - Marketplace de Mangás  
**Status**: ⚠️ CRÍTICO - Múltiplas vulnerabilidades encontradas

---

## 📊 Resumo Executivo

| Severidade | Quantidade |
|-----------|-----------|
| 🔴 **CRÍTICA** | 5 |
| 🟠 **ALTA** | 6 |
| 🟡 **MÉDIA** | 6 |
| 🟢 **BAIXA** | 4 |
| **TOTAL** | **21 issues** |

⚠️ **Ação Imediata Necessária**: Os 5 bugs críticos devem ser corrigidos antes de qualquer deployment para produção.

---

## 🔴 BUGS CRÍTICOS

### 1️⃣ Quantidade Negativa no Carrinho
**Ficheiro**: [assets/controller/controllerCarrinho.php](assets/controller/controllerCarrinho.php#L29)  
**Linhas**: 29-35  
**Severidade**: 🔴 CRÍTICA

**Problema**:
```php
$quantidade = intval($_POST['quantidade'] ?? 1);
// ... sem validação ...
$result = ModelCarrinho::adicionar($userId, $produtoId, $quantidade);
```

- Não há validação se `$quantidade <= 0`
- Um utilizador pode adicionar quantidade negativa ao carrinho
- Isto permite "remover" produtos ou manipular preços

**Impacto**: Fraude financeira direta - permitir quantidades negativas = dinheiro negativo

**Sugestão de Fix**:
```php
$quantidade = intval($_POST['quantidade'] ?? 1);
if ($quantidade <= 0) {
    jsonResponse(['success' => false, 'message' => 'Quantidade deve ser maior que zero.'], 400);
}
```

---

### 2️⃣ Comparação Loose (==) em Autenticação
**Ficheiro**: [assets/controller/controllerSuporte.php](assets/controller/controllerSuporte.php#L79)  
**Linhas**: 79, 104, 127  
**Severidade**: 🔴 CRÍTICA

**Problema**:
```php
if (($_SESSION['user_role'] ?? '') !== 'admin' && $ticket['utilizador_id'] != $_SESSION['user_id']) {
    jsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
}
```

- Usa `!=` (loose comparison) em vez de `!==` (strict comparison)
- Em PHP: `"0" == 0` retorna `true`
- Se um utilizador tiver ID=0 e a sessão tiver user_id="0", a comparação passa
- Permite bypass de autenticação

**Impacto**: Acesso não autorizado a tickets de outros utilizadores

**Sugestão de Fix**:
```php
if (($_SESSION['user_role'] ?? '') !== 'admin' && (int)$ticket['utilizador_id'] !== (int)$_SESSION['user_id']) {
```

---

### 3️⃣ Falta de Type Casting em Filtros de Preço
**Ficheiro**: [assets/controller/controllerMangas.php](assets/controller/controllerMangas.php#L19-L20)  
**Linhas**: 19-20  
**Severidade**: 🔴 CRÍTICA

**Problema**:
```php
'preco_min' => $_GET['preco_min'] ?? '',
'preco_max' => $_GET['preco_max'] ?? '',
```

Em [assets/model/modelmangas.php](assets/model/modelmangas.php#L30-L35):
```php
if (!empty($filtros['preco_min'])) {
    $where[] = "p.preco >= ?";
    $params[] = $filtros['preco_min'];  // ⚠️ Strings, não números!
}
```

- Os valores são strings, não floats
- Comparações SQL podem não funcionar corretamente
- Um valor como "999999999999" pode retornar resultados incorretos

**Impacto**: Injeção lógica em queries, resultados incorretos

**Sugestão de Fix**:
```php
'preco_min' => !empty($_GET['preco_min']) ? floatval($_GET['preco_min']) : null,
'preco_max' => !empty($_GET['preco_max']) ? floatval($_GET['preco_max']) : null,
```

---

### 4️⃣ Dados de Entrega Vazios em Encomendas
**Ficheiro**: [assets/controller/controllerCarrinho.php](assets/controller/controllerCarrinho.php#L78-L84)  
**Linhas**: 78-84  
**Severidade**: 🔴 CRÍTICA

**Problema**:
```php
$morada       = trim($_POST['morada'] ?? '');
$cidade       = trim($_POST['cidade'] ?? '');
$codigoPostal = trim($_POST['codigo_postal'] ?? '');
$telefone     = trim($_POST['telefone'] ?? '');

// Sem validação - podem estar todos vazios!
$result = ModelCarrinho::criarEncomenda($userId, $stripeToken, $metodo, $morada, $cidade, $codigoPostal, $telefone);
```

- Nenhuma validação se os campos não estão vazios
- Uma encomenda pode ser criada sem morada de entrega
- Impossível entregar produtos sem endereço

**Impacto**: Encomendas inviáveis, perda de dados de clientes

**Sugestão de Fix**:
```php
if (empty($morada) || empty($cidade) || empty($codigoPostal) || empty($telefone)) {
    jsonResponse(['success' => false, 'message' => 'Todos os dados de entrega são obrigatórios.'], 400);
}
```

---

### 5️⃣ Falta de Validação de Stock
**Ficheiro**: [assets/model/modelCarrinho.php](assets/model/modelCarrinho.php#L30-L48)  
**Linhas**: 30-48  
**Severidade**: 🔴 CRÍTICA

**Problema**:
```php
public static function adicionar($userId, $produtoId, $quantidade = 1) {
    $db = getDB();
    
    // Valida se produto existe
    $stmt = $db->prepare("SELECT id, stock FROM produtos WHERE id = ? AND ativo = 1");
    $stmt->execute([$produtoId]);
    $produto = $stmt->fetch();
    if (!$produto) {
        return ['success' => false, 'message' => 'Produto não encontrado.'];
    }
    
    // ⚠️ NÃO valida se há stock suficiente!
    // $produto['stock'] é lido mas nunca usado
    $stmt = $db->prepare("INSERT INTO carrinho ...");
```

- Stock é consultado mas nunca validado
- Um utilizador pode adicionar 1000 unidades de um produto mesmo que haja apenas 5 em stock
- Permite over-selling

**Impacto**: Fraude de over-selling, conflito com encomendas

**Sugestão de Fix**:
```php
if ($quantidade > $produto['stock']) {
    return ['success' => false, 'message' => 'Stock insuficiente para esta quantidade.'];
}
```

---

## 🟠 BUGS DE ALTA SEVERIDADE

### 6️⃣ Credenciais Hardcoded na Base de Dados
**Ficheiro**: [assets/config/database.php](assets/config/database.php#L260-L262)  
**Linhas**: 260-262  
**Severidade**: 🟠 ALTA

**Problema**:
```php
$conn = new mysqli('localhost', 'root', '', 'mangaverse_db');
```

- Username: `root` (utilizador administrativo)
- Password: vazia (sem senha)
- Isto é uma vulnerability crítica de segurança
- Qualquer um com acesso ao código pode aceder à base de dados como administrador

**Impacto**: Acesso não autorizado à base de dados completa

**Sugestão de Fix**:
- Mover credenciais para variáveis de ambiente (`.env`)
- Criar um utilizador específico da aplicação com permissões limitadas
```php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'manga_app';
$db_pass = getenv('DB_PASS') ?: '';
```

---

### 7️⃣ Função Deprecated mime_content_type()
**Ficheiro**: [assets/config/database.php](assets/config/database.php#L140)  
**Linha**: 140  
**Severidade**: 🟠 ALTA

**Problema**:
```php
$mime = mime_content_type($file['tmp_name']) ?: '';
```

- `mime_content_type()` foi deprecated em PHP 5.3 e removida em PHP 7.0+
- Se o servidor usar PHP 7.0+, isto gera um erro fatal
- A função pode não estar disponível em alguns ambientes

**Impacto**: Erro fatal em validação de uploads

**Sugestão de Fix**:
```php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
```

---

### 8️⃣ Variável $_POST['badge'] Sem Validação
**Ficheiro**: [assets/controller/controllerMangas.php](assets/controller/controllerMangas.php#L66)  
**Linhas**: 66, 70  
**Severidade**: 🟠 ALTA

**Problema**:
```php
'badge'        => $_POST['badge'] ?? null,
'condicao'     => $_POST['condicao'] ?? 'novo',
```

- Nenhuma validação whitelist
- Um utilizador pode enviar qualquer string
- Exemplo: `badge: "<script>alert('xss')</script>"`
- Quando renderizado em HTML, pode executar JavaScript

**Impacto**: Potencial XSS (Cross-Site Scripting)

**Sugestão de Fix**:
```php
$allowedBadges = ['novo', 'promocao', 'bestseller', 'limitado'];
$badge = in_array($_POST['badge'] ?? '', $allowedBadges, true) ? $_POST['badge'] : null;

$allowedCondicoes = ['novo', 'como-novo', 'ligeiramente-usado', 'usado'];
$condicao = in_array($_POST['condicao'] ?? 'novo', $allowedCondicoes, true) ? $_POST['condicao'] : 'novo';
```

---

### 9️⃣ Variável Não Utilizada $codigoPromo
**Ficheiro**: [assets/controller/controllerCarrinho.php](assets/controller/controllerCarrinho.php#L82)  
**Linha**: 82  
**Severidade**: 🟠 ALTA

**Problema**:
```php
$codigoPromo  = $_POST['codigo_promo'] ?? '';
// ... código não usa $codigoPromo nunca!
$result = ModelCarrinho::criarEncomenda(...); // não passa código promo
```

- Variável é lida mas nunca usada
- Funcionalidade incompleta
- Código promo não é aplicado ao cálculo de preço
- Isto pode levar a perdas financeiras se clientes esperam descontos

**Impacto**: Funcionalidade incompleta, possível perda de vendas

**Sugestão de Fix**:
- Implementar validação e aplicação de código promo:
```php
$codigoPromo = trim($_POST['codigo_promo'] ?? '');
$desconto = 0;
if (!empty($codigoPromo)) {
    $desconto = ModelCarrinho::validarCodigoPromo($codigoPromo);
}
```

---

### 🔟 Cálculo Incorreto de IVA
**Ficheiro**: [assets/model/modelCarrinho.php](assets/model/modelCarrinho.php#L108-L114)  
**Linhas**: 108-114  
**Severidade**: 🟠 ALTA

**Problema**:
```php
$subtotal = 0;
foreach ($carrinho as $item) {
    $subtotal += $item['preco'] * $item['quantidade'];
}

$envio = $subtotal > 30 ? 0 : 3.99;
$iva = $subtotal * 0.23;
$total = $subtotal + $envio;  // ⚠️ IVA não é adicionado!
```

- IVA é calculado mas não é adicionado ao total final
- O cliente é cobrado sem IVA, criando discrepância contabilística
- Pode violar regulações fiscais (CIVA)

**Impacto**: Fraude fiscal, violação de regulações

**Sugestão de Fix**:
```php
$total = $subtotal + $envio + $iva;
```

---

### 1️⃣1️⃣ Conexão MySQLi Legacy Não Utilizada
**Ficheiro**: [assets/config/database.php](assets/config/database.php#L260-L262)  
**Linhas**: 260-262  
**Severidade**: 🟠 ALTA

**Problema**:
```php
// ── Compatibilidade: manter $conn para código legado ─────
$conn = new mysqli('localhost', 'root', '', 'mangaverse_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
```

- Cria uma segunda conexão à base de dados (além da PDO)
- Nunca é usada (todo o código usa PDO)
- Desperdiça recursos e memória
- Cria confusão no código (duas conexões diferentes)

**Impacto**: Desperdício de recursos, possíveis limites de conexões atingidos

**Sugestão de Fix**:
```php
// Remover completamente esta secção ou refatorar código antigo para usar PDO
```

---

## 🟡 BUGS DE SEVERIDADE MÉDIA

### 1️⃣2️⃣ Falta de Proteção CSRF
**Ficheiro**: Todos os controllers  
**Severidade**: 🟡 MÉDIA

**Problema**:
- Nenhum token CSRF gerado ou validado
- Embora use mostly POST, não há proteção contra CSRF attacks
- Um atacante pode fazer um utilizador autenticado executar ações involuntariamente

**Impacto**: Cross-Site Request Forgery attacks possíveis

**Sugestão de Fix**:
```php
// Em database.php
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// Nos controllers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'Token inválido.'], 403);
    }
}
```

---

### 1️⃣3️⃣ Falta de Rate Limiting
**Ficheiro**: Todos os controllers (especialmente Auth, Contacto, Suporte)  
**Severidade**: 🟡 MÉDIA

**Problema**:
- Nenhum rate limiting em endpoints
- Um utilizador pode fazer brute force em login
- Um atacante pode fazer spam em contacto/suporte

**Impacto**: Força bruta de passwords, spam, DoS

**Sugestão de Fix**:
```php
function checkRateLimit($key, $limit = 10, $window = 3600) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE key = ? AND created_at > ?");
    $stmt->execute([$key, time() - $window]);
    $result = $stmt->fetch();
    
    if ($result['count'] >= $limit) {
        jsonResponse(['success' => false, 'message' => 'Demasiadas tentativas. Tenta mais tarde.'], 429);
    }
    
    $stmt = $db->prepare("INSERT INTO rate_limits (key, created_at) VALUES (?, ?)");
    $stmt->execute([$key, time()]);
}
```

---

### 1️⃣4️⃣ Validação Incompleta de Cores
**Ficheiro**: [assets/controller/controllerMangas.php](assets/controller/controllerMangas.php#L68-L69)  
**Linhas**: 68-69  
**Severidade**: 🟡 MÉDIA

**Problema**:
```php
'cor1'         => $_POST['cor1'] ?? '#0a0a0a',
'cor2'         => $_POST['cor2'] ?? '#e8002d',
```

- Nenhuma validação se são cores hexadecimais válidas
- Um utilizador pode enviar `cor1: "../../../etc/passwd"`
- Dependendo de como é renderizado, pode levar a problemas

**Impacto**: Possível path traversal ou injeção

**Sugestão de Fix**:
```php
$validarCor = function($cor) {
    return preg_match('/^#[0-9a-f]{6}$/i', $cor) ? $cor : null;
};

'cor1' => $validarCor($_POST['cor1'] ?? '') ?? '#0a0a0a',
'cor2' => $validarCor($_POST['cor2'] ?? '') ?? '#e8002d',
```

---

### 1️⃣5️⃣ Output Não Escapado em erro.php
**Ficheiro**: [erro.php](erro.php#L5)  
**Linha**: 5  
**Severidade**: 🟡 MÉDIA

**Problema**:
```php
$errorCode    = $_GET['code'] ?? '500';  // ⚠️ Não escapado!
$errorMessage = $_GET['msg']  ?? 'Ocorreu um erro inesperado.';
```

Depois renderizado no HTML:
```php
echo $errorCode;
```

- `$errorCode` não está escapado com `htmlspecialchars()`
- Um atacante pode executar XSS: `erro.php?code=<script>alert('xss')</script>`

**Impacto**: XSS via parâmetros URL

**Sugestão de Fix**:
```php
$errorCode = htmlspecialchars($_GET['code'] ?? '500', ENT_QUOTES, 'UTF-8');
```

---

### 1️⃣6️⃣ Sem Validação de Email Único em Registo
**Ficheiro**: [assets/model/modelAuth.php](assets/model/modelAuth.php#L10-L15)  
**Linhas**: 10-15  
**Severidade**: 🟡 MÉDIA

**Problema**:
```php
$stmt = $db->prepare("SELECT id FROM utilizadores WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    return ['success' => false, 'message' => 'Este email já está registado.'];
}
```

- Validação existe, mas é feita em runtime sem índice único na base de dados
- Se duas requisições chegarem simultaneamente, ambas podem passar
- Race condition: dois utilizadores podem registar-se com o mesmo email

**Impacto**: Registos duplicados, conflitos de conta

**Sugestão de Fix**:
- Adicionar `UNIQUE KEY` na coluna `email` da tabela `utilizadores`:
```sql
ALTER TABLE utilizadores ADD UNIQUE KEY uk_email (email);
```

---

## 🟢 BUGS DE BAIXA SEVERIDADE

### 1️⃣7️⃣ Inconsistência de Nomes de Ficheiros
**Ficheiro**: `assets/model/`  
**Severidade**: 🟢 BAIXA

**Problema**:
- `modelmangas.php` (lowercase 'mangas')
- `ModelMangas` (classe com camelCase)
- `controlllerMangas.php` existe com typo (3 l's)

**Impacto**: Confusão no código, possível erro em case-sensitive systems

**Sugestão de Fix**:
- Renomear para `modelMangas.php` (camelCase consistente)
- Remover ficheiro duplicado com typo

---

### 1️⃣8️⃣ Sem Paginação em Listagens
**Ficheiro**: [assets/model/modelmangas.php](assets/model/modelmangas.php#L8) e [assets/model/modelSuporte.php](assets/model/modelSuporte.php#L50)  
**Severidade**: 🟢 BAIXA

**Problema**:
```php
public static function getAll($filtros = []) {
    // ... query sem LIMIT
    return $stmt->fetchAll();  // Pode retornar milhares de registos
}
```

- Sem limite de registos
- Com 10.000 mangás, retorna todos
- Causa problemas de performance e memória

**Impacto**: Degradação de performance com grande volume de dados

**Sugestão de Fix**:
```php
$limit = min(50, intval($filtros['limit'] ?? 50));
$offset = max(0, intval($filtros['offset'] ?? 0));
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
```

---

### 1️⃣9️⃣ Sem Logging de Ações Críticas
**Ficheiro**: Todos os models  
**Severidade**: 🟢 BAIXA

**Problema**:
- Sem audit log para criar/atualizar/eliminar produtos
- Sem log de transações de pagamento
- Impossível rastrear quem fez o quê e quando

**Impacto**: Impossível auditar atividades, compliance issues

**Sugestão de Fix**:
```php
function logAction($userId, $acao, $tabela, $id, $dados = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO audit_log (utilizador_id, acao, tabela, id, dados, criado_em) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $acao, $tabela, $id, json_encode($dados)]);
}

// Quando eliminar
ModelMangas::eliminar($id);
logAction($_SESSION['user_id'], 'DELETE', 'produtos', $id);
```

---

### 2️⃣0️⃣ Sem Versionamento de API
**Ficheiro**: Todos os controllers  
**Severidade**: 🟢 BAIXA

**Problema**:
- Controllers are accessed directly: `assets/controller/controllerMangas.php?acao=listar`
- Sem versioning
- Mudanças futuras vão quebrar clientes antigos

**Impacto**: Quebra de compatibilidade, dificuldade em updates

**Sugestão de Fix**:
- Implementar API versioning:
```
/api/v1/mangas/listar
/api/v1/mangas/detalhe
```

---

### 2️⃣1️⃣ Sem Tratamento de Concorrência em Encomendas
**Ficheiro**: [assets/model/modelCarrinho.php](assets/model/modelCarrinho.php#L95-L140)  
**Severidade**: 🟢 BAIXA

**Problema**:
```php
// Sem lock pessimista
$carrinho = self::getCarrinho($userId);
// ... outro processo pode modificar carrinho aqui ...
foreach ($carrinho as $item) {
    $subtotal += $item['preco'] * $item['quantidade'];
}
```

- Se dois checkout requests chegarem ao mesmo tempo, o carrinho pode ser processado duas vezes
- Race condition: múltiplas encomendas do mesmo carrinho

**Impacto**: Duplicação de encomendas em case de race conditions

**Sugestão de Fix**:
```php
$db->beginTransaction();
$stmt = $db->prepare("SELECT * FROM carrinho WHERE utilizador_id = ? FOR UPDATE");
$stmt->execute([$userId]);
$carrinho = $stmt->fetchAll();
// ... resto da operação ...
$db->commit();
```

---

## 📋 Checklist de Correções Recomendadas

### Imediato (Antes de Deploy)
- [ ] Corrigir bug de quantidade negativa (BUG #1)
- [ ] Corrigir comparação loose em autenticação (BUG #2)
- [ ] Corrigir type casting de preços (BUG #3)
- [ ] Validar dados de entrega obrigatórios (BUG #4)
- [ ] Implementar validação de stock (BUG #5)
- [ ] Mover credenciais para .env (BUG #6)
- [ ] Corrigir cálculo de IVA (BUG #10)

### Curto Prazo (1-2 semanas)
- [ ] Atualizar para finfo_file() (BUG #7)
- [ ] Adicionar whitelist de badge/condição (BUG #8)
- [ ] Implementar código promo (BUG #9)
- [ ] Remover conexão MySQLi legacy (BUG #11)
- [ ] Implementar proteção CSRF (BUG #12)
- [ ] Implementar rate limiting (BUG #13)
- [ ] Corrigir validação de cores (BUG #14)
- [ ] Escapar outputs em erro.php (BUG #15)

### Médio Prazo (1 mês)
- [ ] Adicionar constraint UNIQUE na coluna email (BUG #16)
- [ ] Renomear ficheiros (BUG #17)
- [ ] Implementar paginação (BUG #18)
- [ ] Adicionar audit logging (BUG #19)
- [ ] Implementar API versioning (BUG #20)
- [ ] Implementar locks em checkout (BUG #21)

---

## 🔒 Recomendações Gerais de Segurança

1. **Variáveis de Ambiente**
   - Criar ficheiro `.env` com credenciais
   - Usar `getenv()` para carregar valores sensíveis
   - Nunca commitar `.env` para git

2. **Headers de Segurança**
   - Adicionar `X-Content-Type-Options: nosniff`
   - Adicionar `X-Frame-Options: DENY`
   - Adicionar `Content-Security-Policy`

3. **Preparação para Produção**
   - Desligar error display (`display_errors = Off`)
   - Ativar logging para ficheiro
   - Configurar permissões de ficheiros (644 para files, 755 para dirs)

4. **Atualizações Periódicas**
   - Manter PHP atualizado (PHP 8.0+)
   - Revisar dependências regularmente
   - Aplicar patches de segurança

5. **Testes de Segurança**
   - Implementar testes unitários para validações
   - Realizar pentesting trimestral
   - Usar ferramentas como OWASP ZAP

---

## 📞 Contacto para Esclarecimentos

Para mais detalhes sobre qualquer issue, consulte o código-fonte na localização específica indicada.

---

**Relatório Gerado**: 6 de maio de 2026  
**Versão**: 1.0  
**Status**: ⚠️ CRÍTICO - Múltiplas Ações Necessárias
