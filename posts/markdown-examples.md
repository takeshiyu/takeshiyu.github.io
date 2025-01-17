---
title: Markdown Extension Examples
date: 2025-01-16
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

This page demonstrates some of the built-in markdown extensions provided by VitePress.

---

## Syntax Highlighting

VitePress provides Syntax Highlighting powered by [Shiki](https://github.com/shikijs/shiki), with additional features like line-highlighting:

**Input**

````md
```js{4}
export default {
  data () {
    return {
      msg: 'Highlighted!'
    }
  }
}
```
````

**Output**

```js{4}
export default {
  data () {
    return {
      msg: 'Highlighted!'
    }
  }
}
```

```json
{
  "title": "Runtime API Examples",
  "description": "",
  "frontmatter": {
    "title": "Runtime API Examples",
    "date": "2025-01-16T00:00:00.000Z",
    "author": "Takeshi Yu",
    "gravatar": "3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39",
    "twitter": "@takeshi_hey"
  },
  "headers": [],
  "relativePath": "posts/api-examples.md",
  "filePath": "posts/api-examples.md"
}
```

## Custom Containers

**Input**

```md
::: info
This is an info box.
:::

::: tip
This is a tip.
:::

::: warning
This is a warning.
:::

::: danger
This is a dangerous warning.
:::

::: details
This is a details block.
:::
```

**Output**

::: info
This is an info box.
:::

::: tip
This is a tip.
:::

::: warning
This is a warning.
:::

::: danger
This is a dangerous warning.
:::

::: details
This is a details block.
:::

## More

Check out the documentation for the [full list of markdown extensions](https://vitepress.dev/guide/markdown).
