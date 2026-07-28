# espocrm-massmail-htmlizer

Adds records related to an EspoCRM Mass Email to the standard email-template
placeholder context.

For a Mass Email with a `knowledgeBaseArticle` relation, templates can use for
example:

```text
{KnowledgeBaseArticle.name}
{KnowledgeBaseArticle.body}
```

## Supported EspoCRM versions

The extension supports EspoCRM 9.0.8 and 9.1.9.

The EspoCRM core `SendingProcessor.php` is not included in the extension.
Installation applies a small, version-checked integration patch to the clean
EspoCRM core file. The installer selects the patcher for the installed EspoCRM
version.

Install with:

```bash
CRM_DIR=~/crm ./build-massmail-htmlizer.sh install
```

The patchers are idempotent. They accept either the exact clean core file for
their EspoCRM version or an already fully patched file. They refuse modified,
partial, or unexpected variants.
