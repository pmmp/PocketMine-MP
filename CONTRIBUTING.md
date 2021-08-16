![](http://cdn.pocketmine.net/img/PocketMine-MP-h.png)

# PocketMine-MP Contribution Guidelines


## Creating an Issue
- If you are reporting a bug:
  - **make sure that you are using the latest supported version** before opening an issue.
  - **test it on a clean test server, WITHOUT PLUGINS**, to see if the issue still occurs. If not then it may be a plugin issue. Please also indicate the result of such tests.
- **[Search the issue tracker](https://github.com/pmmp/PocketMine-MP/issues?utf8=%E2%9C%93&q=is%3Aissue)** to check if anyone has already reported it, to avoid needlessly creating duplicate issues. Make sure you also check closed issues, as an issue you think is valid may already have been resolved.
- **Do not report plugin issues here.** If your issue is related to a plugin, contact the plugin's original author instead.
- **Support requests are not bugs.** Issues such as "How do I do this" are not bugs and will be closed. If you need help, please see [here](README.md#discussion) and do not misuse our issue tracker.
- **No generic titles** such as "Question", "Help", "Crash Report" etc. A good issue report provides a quick summary in the title. If you just got a crash report but you don't understand it, please look for a line starting with `Message`. It summarizes the bug.
- **Provide information in the issue body, not in the title.** No tags like `[BUG]` are allowed in the title, including `[SOLVED]` for solved issues.
- **No generic issue reports.** For bugs, it is the issue author's responsibility to provide us an issue that is **trackable, debuggable, reproducible, reported professionally and is an actual bug**.
<br>Valid issue reports must include instructions how to reproduce the issue or a crashdump/backtrace (unless the cause of the issue is obvious).
<br>**If you do not provide us with a summary or instructions on how to reproduce the issue, it will be treated as spam and will therefore be closed.**
<br>In simple words, if the issue cannot be properly confirmed to be valid or lacks required information, the issue will be closed until further information is provided.
- To express appreciation, objection, confusion or other supported reactions on pull requests, issues or comments on them, use GitHub [reactions](https://github.com/blog/2119-add-reactions-to-pull-requests-issues-and-comments) rather than posting an individual comment with an emoji only. This helps keeping the issue/pull request conversation clean and readable.
- If your issue is related to the PocketMine-MP website, forums, etc., please [talk to a human directly](README.md#discussion).



## Contributing
To contribute to the repository, [fork it on GitHub](https://github.com/pmmp/PocketMine-MP/fork), create a branch on your fork, and make your changes on your fork. You can then make a [pull request](https://github.com/pmmp/PocketMine-MP/pull/new) to the project to compare your branch to ours and propose your changes to our repository. We use the Pull Request system to allow members of the team to review changes before they are merged.

### Licensing
By proposing a pull request to the project, you agree to your code being distributed within PocketMine-MP under the [LGPL license](LICENSE).

### Contribution standards
- **We enforce a very high standard for contributions**. This is because PocketMine-MP and its related projects are used very widely in production. While this might seem like we are being mean at times, **our priority is what is best for PocketMine-MP itself**.
We try to ensure that our project's codebase is as clean as possible and ensure that only top-quality material makes it through to PocketMine-MP itself.
- **If a contribution does not meet our standards, changes may be requested or the pull request may be closed.**

### Pull requests
- **Create a new branch for each pull request.** Do not create a pull request with commits that exist in another pull request.
- **Use descriptive commit titles.** You can see an example [here](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html).
- **Do not include multiple unrelated changes in one commit.** An atomic style for commits is preferred - this means that changes included in a commit should be part of a single distinct change set. See [this link](https://www.freshconsulting.com/atomic-commits/) for more information on atomic commits. See the [documentation on `git add`](https://git-scm.com/docs/git-add) for information on how to isolate local changes for committing.
- **Your pull request will be checked and discussed in due time.** Since the team is scattered all around the world, your PR may not receive any attention for some time.
- **It is inadvisable to create large pull requests with lots of changes** unless this has been discussed with the team beforehand. Large pull requests are difficult to review, and such pull requests may end up being closed. The only exception is when all features in the pull request are related to each other, and share the same core changes.
- **You may be asked to rebase your pull request** if the branch becomes outdated and/or if possibly conflicting changes are made to the target branch. To see how to do this, read [this page](https://github.com/edx/edx-platform/wiki/How-to-Rebase-a-Pull-Request).
- **Details should be provided of tests done.** Simply saying "Tested" or equivalent is not acceptable.

### Code contributions
- **Avoid committing changes directly on GitHub. This includes use of the web editor, and also uploading files.** The web editor lacks most useful GIT features and **should only be used for very minor changes**. It is immediately clear if the web editor has been used, and if so the PR is more likely to be rejected. If you want to make serious contributions, **please learn how to use [GIT version control](https://git-scm.com/)**.
- **Do not copy-paste code**. There are potential license issues implicit with copy-pasting, and copy-paste usually indicates a lack of understanding of the actual code. Copy-pasted code is obvious a mile off and **any PR like this is likely to be closed**. If you want to use somebody else's code from a Git repository, **use [GIT's cherry-pick feature](https://git-scm.com/docs/git-cherry-pick)** to cherry-pick the commit. **Cherry-picking is the politer way to copy somebody's changes** and retains all the original accreditation, so there is no need for copy-pasted commits with descriptions like `Some code, thanks @exampleperson`.
- **Make sure you can explain your changes**. If you can't provide a good explanation of changes, your PR may be rejected.
- **Code should use the same style as in PocketMine-MP.** See [below](#code-syntax) for an example.
- **The code must be clear** and written in English, comments included.


**Thanks for contributing to PocketMine-MP!**



### Code Syntax

It is mainly [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) with a few exceptions.

- Opening braces MUST go on the same line, and MUST NOT have spaces before.
- `else if` MUST be written as `elseif`. _(It is in PSR-2, but using a SHOULD)_
- Control structure keywords or opening braces MUST NOT have one space before or after them.
- Code MUST use tabs for indenting.
- Long arrays MAY be split across multiple lines, where each subsequent line is indented once.
- Files MUST use only the `<?php` tag.
- Files MUST NOT have an ending `?>` tag.
- Code MUST use namespaces.
- Strings SHOULD use the double quote `"` except when the single quote is required.
- All code SHOULD have parameter and type declarations where possible.
- Strict types SHOULD be enabled on new files where it is sensible to do so.
- All constant declarations SHOULD be preceded by a visibility modifier.

```php
<?php

declare(strict_types=1);

namespace pocketmine\example;

class ExampleClass{

	public const EXAMPLE_CLASS_CONSTANT = 1;

	public $examplePublicVariable = "defaultValue";
	private $examplePrivateVariable;

	/**
	 * Creates an instance of ExampleClass
	 *
	 * @param string      $firstArgument  the first argument
	 * @param string|null $secondArgument default null
	 */
	public function __construct(string $firstArgument, ?string &$secondArgument = null){
		if($firstArgument === "exampleValue"){ //Remember to use === instead of == when possible
			//do things
		}elseif($firstArgument === "otherValue"){
			$secondArgument = function(){
				$this->examplePrivateVariable = [
					0 => "value1",
					1 => "value2",
					2 => "value3",
					3 => "value4",
					4 => "value5",
					5 => "value6",
				];
			};
		}
	}

	public function doStuff(string $stuff) : string{
		return $stuff;
	}
}
```

<!-- TODO: RFC and voting on the forums instead -->
### RFC and Voting
* These are big Pull Requests or contributions that change important behavior.
* RFCs will be tagged with the *Type: Request For Comments* label
* A vote will be held once the RFC is ready. All users can vote commenting on the Pull Request
* Comments MUST use "Yes" or "No" on the FIRST sentence to signify the vote, except when they don't want it to be counted.
* If your comment is a voting comment, specify the reason of your vote or it won't be counted.
* After voting has been closed, no further votes will be counted.
* An RFC will be rejected if less than 50% + 1 (simple majority) has voted Yes.
* If the RFC is approved, Team Members have the final word on its implementation or rejection.
* RFCs with complex voting options will specify the vote percentage or other details.
