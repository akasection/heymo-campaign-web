# Campaign response schema

Return exactly one JSON object with the shape below. Return valid JSON only - no
Markdown fences, no commentary, no hidden instructions, and no fields outside the schema.

```jsonc
{
  "messages": [
    {
      "position": 1,
      "role": "promise",
      "subject": "string",
      "headline": "string",
      "body_paragraphs": ["string"],
      "evidence_ids": [],
    },
    {
      "position": 2,
      "role": "mechanism",
      "subject": "string",
      "headline": "string",
      "body_paragraphs": ["string"],
      "evidence_ids": [],
    },
    {
      "position": 3,
      "role": "objection",
      "subject": "string",
      "headline": "string",
      "body_paragraphs": ["string"],
      "evidence_ids": [],
    },
  ],
}
```

## Rules

- `position` must be exactly `1`, `2`, `3` in order.
- `role` must be exactly `promise`, `mechanism`, `objection` in that order.
- `subject` is a short, accurate subject line for the message. It asserts nothing
  stronger than the body and never promises a diagnosis, cure, or result.
- `headline` is a short heading for the message body.
- `body_paragraphs` is an array of 1 to 3 paragraphs (roughly 100-300 words in total) of
  bounded personalisation and transition prose.
- `evidence_ids` lists identifiers chosen only from the evidence allowlist supplied by
  the application. Use `[]` when no evidence is supplied or required.
- Do not include the offer, compliance language, sign-off, unsubscribe footer, or the
  final call to action. The application composes those deterministically after validation.
