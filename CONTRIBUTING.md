![](http://cdn.pocketmine.net/img/PocketMine-MP-h.png)

# PocketMine-MP Contribution Guidelines

You must follow these guidelines if you wish to contribute to the PocketMine-MP code base, or participate in issue tracking.

## I have a question
* For questions, please refer to the _#pmmp_ or _#pocketmine_ IRC channel on Freenode. There is a [WebIRC](http://webchat.freenode.net?channels=pmmp,pocketmine&uio=d4) if you do not want to install an IRC client.
* You can ask directly to _[@PocketMine](https://twitter.com/PocketMine)_ in Twitter, but don't expect an immediate reply.
* You may use our [Forum](https://forums.pmmp.io) to ask questions.
* We do not accept questions or support requests in our issue tracker.

## Creating an Issue
* First, use the [Issue Search](https://github.com/pmmp/PocketMine-MP/search?ref=cmdform&type=Issues) to check if anyone has reported it. Check also closed issues, as an issue you think is valid may actually be invalid.
  * If an issue has been _fixed_ and closed, create another issue.
* If your issue is related to a plugin, do **not** report here. Contact the plugin's original author instead.
* **Support requests are not bugs.** Issues such as "How do I do this" are not bugs and are closed as soon as a collaborator spots it. They are referred to our Forum to seek assistance. Please refer to the section [I have a quesetion](#i-have-a-question) instead.
* **No generic titles** such as "Question", "Help", "Crash Report" etc.
  * If you just got a crash report but you don't understand it, please look for a line starting with `Message`. It summarizes the bug.
* Information must be provided in the issue body, not in the title. No tags like `[BUG]` are allowed in the title, including `[SOLVED]` for solved issues.
* Similarly, no generic issue reports. For bugs, it is the issue author's responsibility to provide us an issue that is **trackable, debuggable, reproducible, reported professionally and is an actual bug**. If you do not provide us with a summary or instructions on how to reproduce the issue, it is a support request until the actual bug has been found and therefore the issue is closed.
  * In simple words, if your issue does not appear to be a bug or a feature request, or if the issue cannot be properly confirmed to be valid, the issue will be closed until further information is provided.
* To express appreciation, objection, confusion or other supported reactions on pull requests, issues or comments on them, use GitHub [reactions](https://github.com/blog/2119-add-reactions-to-pull-requests-issues-and-comments) rather than posting an individual comment with an emoji only. This helps keeping the issue/pull rqeuest conversation clean and readable.
* If your issue is related to the Pocketmine-MP website, forums, etc., please [talk to a human directly](#i-have-a-question).

## Contributing Code
* Use the [Pull Request](https://github.com/pmmp/PocketMine-MP/pull/new) system, your request will be checked and discussed.
* Create each pull request on a new branch. Do not create a pull request on commits that exist in another pull request.
* Code should use the same syntax as in PocketMine-MP. See below for an example.
* The code must be clear and written in English, comments included.
* Use descriptive commit titles
* **Keep each pull request only contain one feature**. The only exception is when all features in the pull request are related to each other, and share the same core changes.
* **Do not create pull requests that only bump the MCPE version**. If it is ready to be updated, the team will update the values directly. Do not change the MCPE version or protocol version in a pull request, unless you have updated the protocol (all packets) entirely.

**Thanks for contributing to PocketMine-MP!**

### Code Syntax

It is mainly [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) with a few exceptions.

* Opening braces MUST go on the same line, and MUST NOT have spaces before.
* `else if` MUST be written as `elseif`. _(It is in PSR-2, but using a SHOULD)_
* Control structure keywords or opening braces MUST NOT have one space before or after them.
* Code MUST use tabs for indenting.
* Long arrays MAY be split across multiple lines, where each subsequent line is indented once. 
* Files MUST use only the `<?php` tag.
* Files MUST NOT have an ending `?>` tag.
* Code MUST use namespaces.
* Strings SHOULD use the double quote `"` except when the single quote is required.

```php
<?php 

namespace pocketmine\example;

class ExampleClass{
	const EXAMPLE_CLASS_CONSTANT = 1;
	public $examplePublicVariable = "defaultValue";
	private $examplePrivateVariable;
	
	/**
	 * Creates an instance of ExampleClass
	 * @param string      $firstArgument  the first argument
	 * @param string|null $secondArgument default null
	 */
	public function __construct($firstArgument, &$secondArgument = null){
		if($firstArgument === "exampleValue"){ //Remember to use === instead == when possible
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

}
```

<!-- TODO: RFC and voting on the forums instead -->
### RFC and Voting
* These are big Pull Requests or contributions that change important behavior.
* RFCs will be tagged with the *PR: RFC* label
* A vote will be held once the RFC is ready. All users can vote commenting on the Pull Request
* Comments MUST use "Yes" or "No" on the FIRST sentence to signify the vote, except when they don't want it to be counted.
* If your comment is a voting comment, specify the reason of your vote or it won't be counted.
* After voting has been closed, no further votes will be counted.
* An RFC will be rejected if less than 50% + 1 (simple majority) has voted Yes.
* If the RFC is approved, Team Members have the final word on its implementation or rejection.
* RFCs with complex voting options will specify the vote percentage or other details.


## Bug Tracking for Collaborators

### Labels
To provide a concise bug tracking environment, prevent the issue tracker from over flowing and to keep support requests out of the bug tracker, PocketMine-MP uses a label scheme a bit different from the default GitHub Issues labels.

Labels are used to identify the type and status of issues and pull requests.

#### Categories
Multiple category labels may be applied to a single issue (but try to keep this to a minimum and do not overuse category labels).
These labels may be applied to issues with bugs related to these, or pull requests that alter the behaviour of these or fix an issue with the same label applied.
- `Category: Core` - Related to PocketMine-MP's core functionality, but not gameplay or API.
- `Category: Gameplay` - Related to gameplay.
- `Category: API` - Related to plugin API.
- `Category: Client` - Related to client functionality or behaviour. This label may be applied to issues reporting client-related bugs, or PRs related to the client such as a protocol update. There are several sub-labels to account for the differences between supported clients.
	- `all`: Related to all versions of the client.
	- `W10`: Related only to Windows 10 Edition Beta.
	- `PE`: Related only to the Pocket Edition
	- `beta`: Related only to a beta version of the game.
	- `other`: Related only to an uncommon PE-compatible version of the client, such as VR Edition.
- `Category: PHP` - Applied to issues caused by the PHP interpreter or its extensions.
- `Category: Other` - Applied to issues which cannot be categorized with any of the above labels.

#### Pull Requests
Pull Request labels are prefixed by `PR:`. Only one label may be applied for a Pull Request.
- `PR: Bug Fix` - The Pull Request fixes a bug. 
- `PR: Contribution` - The Pull Request contributes new features or improvements, but does not fix a bug, nor controversial enough to be an RFC.
- `PR: RFC` - Request for Comments. Refer to [RFC and Voting](#rfc-and-voting).

#### Status
Status labels show the status of the issue. Multiple status labels may be applied.

*Issues*
- `Status: Unconfirmed` - reported issues that have yet to be reproduced by a developer successfully.
- `Status: Reproduced` - a bug that has been reproduced by a developer, or that multiple people are reporting the same issue and symptoms in which case it is automatically assumed that the bug has been reproduced in different environments.
- `Status: Debugged` - the cause of the bug has been found, but has not yet been fixed.
- `Status: Resolved` - applied to *valid* issues which have been fixed. *This label may only be applied to a closed issue.*

*Pull requests*
- `Status: Insufficiently tested` - applied for pull requests that have not undergone tests strict enough.

#### Resolution
Resolution labels show the resolution of an issue or pull request. These labels may only be applied to closed issues.
- `Resolution: Invalid` - Applied to support request issues or issues not related to PocketMine.
- `Resolution: Duplicate` - Applied to issues reporting the same problems as another issue.
- `Resolution: Fixed` - Applied to an issue reporting a bug which has been fixed.
- `Resolution: Works As Intended` - Applied to issues reporting false bugs which work the way they are intended to.
- `Resolution: Won't Fix` - Applied to issues reporting bugs which will not be fixed, for example if the bug is beneficial.
- `Resolution: Obsolete` - Applied to pull requests which are rendered unnecessary by other changes, such as another, similar pull request being merged.

#### Miscellaneous
- `Enhancement` - Applied to issues with viable feature requests or TODO lists.
- `High priority` - Applied to issues or PRs related to critical bugs.

### Closing Issues
To keep the bug tracker clear of non-related issues and to prevent it from overflowing, **issues must be closed as soon as possible** (This may sound unethical, but it is MUCH better than having the BUG TRACKER filled with SUPPORT REQUESTS and "I NEED HELP").

If an issue does not conform to the "Creating an Issue" guidelines above, the issue should be closed.
<!--
### Milestones
PocketMine-MP uses GitHub Milestones to set a goal for a new release. A milestone is set on the following occasions.

- A new Beta release
- A new Stable release

A milestone must use the following format:
```
<version_number> [release_title][release_version]
```
For example:
```
1.6.1b1
```
-->
