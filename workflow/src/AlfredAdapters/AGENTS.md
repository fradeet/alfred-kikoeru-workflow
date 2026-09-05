# ◉ AlfredAdapters 文件夹

这里存放了 Alfred 相关的处理逻辑。


## 目录结构

	AlfredAdapters/
	├── Support/   # 用于储存共享逻辑的地方
	├── Type/      # 储存 Alfred 返回格式的地方，例如 Script Filter 与 Text View
	└── foo.php    # 从 Alfred 中调用的 CLI 入口，设置了 Shebang

- Alfred 会直接调用脚本，有时会传入一个参数给脚本。
- 若脚本遇到报错，需要通过 Alfred 的格式返回错误信息。
- Alfred 通过 STDOUT 获取结果，警告等无关内容需要输出至 STDERR。


## 相关文档

[alfredapp.com/help/workflows/inputs/script-filter/json/](https://www.alfredapp.com/help/workflows/inputs/script-filter/json/)

[Text View JSON Format - Alfred Help and Support](https://www.alfredapp.com/help/workflows/user-interface/text/json/)

[Workflow Script Environment Variables - Alfred Help and Support](https://www.alfredapp.com/help/workflows/script-environment-variables/)
