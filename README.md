# Laravel Upload Manager

[![Tests](https://github.com/LuizHenriqueDigital/laravel-upload-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/LuizHenriqueDigital/laravel-upload-manager/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Um gerenciador de uploads fluente e elegante para Laravel. Simplifique o tratamento de arquivos com uma API intuitiva, suporte a múltiplos arquivos, controle de visibilidade e padrões de nomes personalizáveis.

## ✨ Recursos

- 🚀 **Interface Fluente**: API expressiva e fácil de usar.
- 📁 **Múltiplos Arquivos**: Suporte nativo para arrays de arquivos.
- 🔒 **Visibilidade**: Controle total sobre arquivos públicos ou privados.
- 🎨 **Padrões Dinâmicos**: Use placeholders como `{user_id}`, `{date}`, `{random}`, `{hash}` e mais.
- 🔄 **Resolução de Conflitos**: Incremento automático de nomes para evitar sobrescrita (opcional).
- 📦 **Resultados Estruturados**: Retorna Collections de `UploadedFileResult` (DTO).
- 🛠️ **PSR-12**: Código limpo e padronizado.

## 📦 Instalação

Instale o pacote via composer:

```bash
composer require luizhenriquedigital/laravel-upload-manager
```

## 🚀 Uso Básico

### Arquivos Públicos
Ideal para fotos de perfil, assets de sites, etc.

```php
use LuizHenriqueDigital\UploadManager\Facades\Upload;

Upload::make($request->file('avatar'))
    ->path('profiles')
    ->asPublic()
    ->store();
```

### Arquivos Privados
Ideal para documentos sensíveis.

```php
Upload::make($request->file('rg_copy'))
    ->path('legal/documents')
    ->asPrivate() // Opcional, é o padrão
    ->store();
```

### Exemplo Completo em um Controller

```php
public function store(Request $request)
{
    // O manager aceita um único arquivo ou um array de arquivos automaticamente
    $arquivos = Upload::make($request->file('anexos'))
        ->disk('s3')
        ->path('contratos/v1')
        ->filePattern('cli-{user_id}-{random}')
        ->asPublic()
        ->overwrite(false) // Resolve conflitos adicionando -1, -2 ao nome
        ->store();

    // O retorno é uma Collection de UploadedFileResult (DTO)
    // Você pode salvar no banco com facilidade
    $arquivos->each(fn($file) => Document::create((array) $file));

    return response()->json([
        'message' => 'Upload concluído!',
        'data' => $arquivos
    ]);
}

### Rollback Automático

Em caso de erro na sua lógica de negócio (ex: falha ao salvar no banco), você pode remover facilmente os arquivos que acabaram de ser enviados:

```php
try {
    $arquivos = Upload::make($request->file('docs'))->store();
    
    // Simula um erro ao salvar no banco
    throw new \Exception("Erro ao processar dados");
} catch (\Exception $e) {
    // Remove todos os arquivos do upload atual fisicamente do disco
    Upload::rollback($arquivos);
    
    return response()->json(['error' => $e->getMessage()], 500);
}
```
```

## 🛠️ Customização de Nomes (Placeholders)

Você pode personalizar como os arquivos são salvos usando placeholders no `filePattern()`:

| Placeholder | Descrição | Exemplo |
| :--- | :--- | :--- |
| `{filename}` | Nome original do arquivo (slugified) | `meu-arquivo` |
| `{date}` | Data atual (Y-m-d) | `2024-03-20` |
| `{uuid}` | Identificador Único | `550e8400-e29b-41d4-a716...` |
| `{random}` | String aleatória (8 caracteres) | `aB3cE5gH` |
| `{user_id}` | ID do usuário logado (ou 'guest') | `42` |
| `{hash}` | MD5 hash do conteúdo do arquivo | `1a2b3c...` |
| `{timestamp}` | Unix Timestamp | `1710979200` |

*Nota: O hash é calculado de forma preguiçosa (lazy), apenas se o placeholder for utilizado.*

## 🧪 Testes

O pacote possui uma suíte de testes robusta. Para rodar os testes:

```bash
./vendor/bin/pest
```

Ou usando Docker (incluído):

```bash
./test.sh
```

## 📄 Licença

O Laravel Upload Manager é um software de código aberto licenciado sob a [MIT license](LICENSE).

---

Feito com ❤️ por [Luiz Henrique Digital](https://github.com/LuizHenriqueDigital)
