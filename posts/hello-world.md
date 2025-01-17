---
title: Commit message suggestions
date: 2025-01-16
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

In the latest release, I've added support for commit message and description suggestions via an integration with OpenAI. Commit looks at all of your changes, and feeds that into the machine with a bit of prompt-tuning to get back a commit message that does a surprisingly good job at describing the intent of your changes.

---

## Reactivity System Optimizations

In 3.5, Vue's reactivity system has undergone another major refactor that achieves better performance and significantly improved memory usage (**-56%**) with no behavior changes. The refactor also resolves stale computed values and memory issues caused by hanging computeds during SSR.

In addition, 3.5 also optimizes reactivity tracking for large, deeply reactive arrays, making such operations up to 10x faster in some cases.

**Details: [PR#10397](https://github.com/vuejs/core/pull/10397), [PR#9511](https://github.com/vuejs/core/pull/9511)**

You’d be responsible for things like:

* Design and build ambitious marketing websites for our open-source projects, commercial products, and events like Tailwind Connect.
* Design and prototype new features for Tailwind CSS to make sure we’re always using the full potential of the platform.
* Create new components and templates for Tailwind UI, taking them all the way from initial concept to shipped.
* Enhance our documentation with visual demos to make it easy for people to understand and apply complex CSS features in their work.
* Teach and inspire our audience by breaking down interesting things you design and build as articles and social media posts.

## Reactive Props Destructure

```php
->withWhereHas('customer', function ($query) use ($inputs) {
    $query->when($inputs->get('customer'), fn ($query, $customer) => $query->where('name', 'LIKE', '%'.$customer.'%'))
        ->when($inputs->get('checkout'), fn ($query, $checkout) => $query->where('checkout_type_id', $checkout))
        ->when($inputs->get('salesman'), function ($query, $salesman) {
            $query->withWhereHas('salesman', function ($query) use ($salesman) {
                $query->where('name', 'LIKE', '%'.$salesman.'%');
            });
        });
})
```

Access to a destructured variable, e.g. count, is automatically compiled into props.count by the compiler, so they are tracked on access. Similar to props.count, watching the destructured prop variable or passing it into a composable while retaining reactivity requires wrapping it in a getter:

```ts
const props = withDefaults(
  defineProps<{
    count?: number
    msg?: string
  }>(),
  {
    count: 0,
    msg: 'hello'
  }
)
```