# espocrm-massmail-htmlizer

Adds records related to an EspoCRM Mass Email to the standard email-template
placeholder context.

For a Mass Email with a `knowledgeBaseArticle` relation, templates can use for
example:

```text
{KnowledgeBaseArticle.name}
{KnowledgeBaseArticle.body}
```

## EspoCRM 9.0.8

The old extension installed a complete copy of EspoCRM's
`SendingProcessor.php`. The 9.0.8 version installs only the module and applies
a small, version-checked integration patch during installation.

Install with:

```bash
CRM_DIR=~/crm ./build-massmail-htmlizer.sh install
```
