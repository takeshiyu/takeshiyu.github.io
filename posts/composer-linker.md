---
title: "Composer Linker: The npm link for PHP Developers"
date: 2025-03-21
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

In modern PHP application development, particularly with the rise of microservices architecture and modular design principles, breaking down large applications into independent packages has become a best practice. This approach not only enhances code maintainability and reusability but also allows team members to focus on specific functional areas.

However, when you start managing multiple interdependent packages, the development workflow can become complex and cumbersome. This article shares how I transformed a real development pain point into a [Composer plugin](https://getcomposer.org/doc/articles/plugins.md) that simplifies the PHP multi-package development experience.

---

## TL;DR

* A Composer plugin [Composer Linker](https://github.com/takeshiyu/composer-linker) that mimics [npm link](https://docs.npmjs.com/cli/v9/commands/npm-link) functionality
* Solves the problem of constantly editing `composer.json` when developing local packages
* `Uses symbolic links` to connect project vendor directories with local package code
* `No need to modify project files`, avoiding accidental Git commits of development configurations
* Install it globally: `composer global require takeshiyu/composer-linker`
* Simple commands: `composer link` in package dir, `composer link vendor/package` in project dir
* View linked packages with `composer linked` (current project), `composer linked --global` (all registered), or `composer linked --all` (all projects)

---

While developing Laravel projects, I frequently split core logic into separate packages to improve code reusability and project organization. However, this approach quickly introduced a challenging problem:

```json
// Project's composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "../my-package"  // Local development path
        }
    ],
    "require": {
        "vendor/my-package": "*"
    }
}
```

When I already have a package installed via Composer in multiple projects, the development process becomes quite tedious. Every time I need to modify the package, I must:

1. Modify the composer.json in each project using that package, adding the local path repository
2. Run composer update to use the local version
3. Perform development and testing
4. Afterward, modify each project's composer.json again to remove the local path

What's worse, if I install new packages during development, I can't simply roll back the composer.json file, as doing so would lose information about newly installed packages. I have to manually edit the file, removing only the local path section, which is error-prone.

```bash
# Installing a new package during development
composer require some/new-package

# After package development, can't simply revert composer.json
# Must manually edit to remove repositories section while preserving new packages
```

I've made this mistake multiple times, accidentally committing local development configurations to Git, resulting in deployment failures and unnecessary troubleshooting. It's a frustrating cycle: modify configuration, develop, forget to revert, deployment fails, fix, develop again...

## Existing Solutions and Their Limitations

The community has developed several solutions, each with its own limitations:

#### Using Composer Path Repository

Still requires modifying composer.json, making it easy to forget to revert changes. In multi-project environments, this process must be repeated for each project.

```json
// composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "../my-package"
        }
    ]
}
```

#### Using Environment Variables

Complex implementation, requires additional environment settings, not intuitive. Additionally, each developer may need different environment configurations.

```json
// composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "%ENV_PACKAGE_PATH%"
        }
    ]
}
```

#### Using Git Hooks

Creating a pre-commit hook to check and prevent committing local paths. But it needs to be set up in each project, can be bypassed, not seamless. While it prevents accidental commits, it doesn't simplify the development workflow.

```bash
#!/bin/bash
if grep -q "\"type\": \"path\"" composer.json; then
    echo "Error: Local path repository detected in composer.json"
    exit 1
fi
```

## Inspiration: npm link's Elegant Solution

The Node.js ecosystem has an elegant solution: [npm link](https://docs.npmjs.com/cli/v9/commands/npm-link). It works by:

1. Running npm link in the package directory to register it globally
2. Running npm link package-name in projects using the package to create a link

The key advantages of this approach are:

1. No need to modify package.json files
2. Uses file system level symbolic links
3. Allows real-time visibility of local package changes
4. Simple, intuitive commands
5. Packages only need to be registered once and can be used in multiple projects

I realized that having a similar solution would make the development process much smoother, especially in multi-project environments.

## Building Composer Linker: An Elegant PHP Package Linking Tool

After research and development, I created [Composer Linker](https://github.com/takeshiyu/composer-linker), a Composer plugin that mimics [npm link](https://docs.npmjs.com/cli/v9/commands/npm-link) functionality.

Key design principles:

1. **No project file modifications:** All configurations are stored in a global directory
2. **Simple command interface:** Usage similar to npm link
3. **File system level operations:** Using symbolic links instead of configuration changes
4. **Global registration mechanism:** Register once, use in multiple projects

First, install the plugin:

```bash
composer global require takeshiyu/composer-linker
```

Register a package in the package directory:

```bash
# In the package directory
cd ~/dev/my-package
composer link
```

Use the local package in a project:

```bash
# In the project directory
cd ~/dev/my-project
composer link vendor/my-package
```

And all link information is centralized in a single JSON file:

```json
{
    "registered_packages": {
        "foo/hello": {
            "path": "/Users/foo/dev/work/hello",
            "autoload": {
                "psr-4": {
                    "Foo\\Hello\\": "src/"
                }
            },
            "time": 1742655618
        }
    },
    "projects": {
        "/Users/foo/dev/work/demo": {
            "linked_packages": {
                "foo/hello": "/Users/foo/dev/work/hello"
            }
        }
    }
}
```

View link status:

```bash
# View links in the current project
composer linked

# View globally registered packages
composer linked --global

# View links in all projects
composer linked --all
```

Unlink a package:

```bash
composer unlink vendor/my-package
```

## Real-World Use Cases

Composer Linker simplifies development workflows in several common scenarios:

#### 1. Multi-Project Shared Core Package

With a core business logic package used by multiple microservices, you can register it once and link it everywhere. All projects immediately see your changes without configuration modifications.

#### 2. Local Development and Testing

When developing new features, you can:

* Work in the package directory
* Link it to your test project with a simple command
* See changes in real-time and iterate quickly
* When finished, simply unlink without touching configuration files

#### 3. Team Collaboration

Different team members can develop different packages simultaneously without configuration conflicts or accidental commits of local development settings.

## Future Improvements

Looking ahead, I plan to enhance [Composer Linker](https://github.com/takeshiyu/composer-linker) by drawing further inspiration from npm link's comprehensive features while adding more developer-friendly capabilities tailored specifically for PHP ecosystem needs.

## Conclusion

[Composer Linker](https://github.com/takeshiyu/composer-linker) solves a practical problem I faced in daily PHP development. While it was created to address a personal workflow issue, I believe it will be helpful to other developers facing similar challenges.

This tool brings a long-missing functionality to the PHP ecosystem, making multi-package development smoother and more seamless. Most importantly, it eliminates the worry about accidentally committing local development configurations.

If you've faced similar challenges, I invite you to try [Composer Linker](https://github.com/takeshiyu/composer-linker) — it may become a valuable tool in your PHP development toolkit. The plugin is available on [GitHub](https://github.com/takeshiyu/composer-linker) and can be installed directly via Composer: `composer global require takeshiyu/composer-linker`.
