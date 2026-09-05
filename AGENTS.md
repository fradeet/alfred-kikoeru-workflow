# ◉ 项目概况

这是一个 Alfred 工作流的 PHP 脚本模版，同时给其他的软件预留的集成空间。

Alfred 通常执行位于 `workflow/src/AlfredAdapters` 的编写的任务脚本，使用命令的方式调用。

# ◉ 项目结构

在执行时，Alfred 的项目根目录是 `workflow`。

	workflow/src/
	├── AlfredAdapters/   # 用于将核心逻辑返回的结果处理为 Alfred 的格式
	├── xxxSdk/           # HTTP 请求 SDK，使用 Saloon
	└── foo.php           # 核心逻辑，独立与各个软件平台的逻辑。

# ◉ 技术要点

- 核心逻辑（平台通用逻辑）与平台独有逻辑分离
- 每个业务单独一个文件，并对应平台中的一个命令，例如 Hello。
- 在核心层，可以独立出共享逻辑为一个新类。


## 测试与验证

主要检查语法静态错误与 PHP 兼容性。

检查静态错误使用 PHPStan。

检查兼容性使用 PHPCompatibility。

	vendor/bin/phpcs -ps src --standard=PHPCompatibility

相关项目：[PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility)

# ◉ 项目架构

项目的一个命令的请求周期，会经过：“命令 -> 核心处理 -> 格式输出”一整个流程。

- 命令与输出：位于 `<xxx>Adapter/` 文件夹，用于接收用户的命令，并输出指定的格式。
- 处理：位于 `src/` 文件夹，处理用户逻辑的命令都在这里。

# ◉ 对外边界

以下内容用于对外交互，修改他们可能会造成不兼容改动，修改时要注意：

- `<xxx>Adapter/xxx.php`：这些是 CLI 调用的入口，如果没有要求需要保持一致。
