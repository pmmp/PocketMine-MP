![](http://cdn.pocketmine.net/img/PocketMine-MP-h.png)

# PocketMine-MP Contribution Guidelines

PocketMine-MP is an open source project, and contributions from the community are welcomed, as long as they comply with our quality standards and licensing.

Code contributions must be submitted using [GitHub Pull Requests](https://github.com/pmmp/PocketMine-MP/pulls), where they will be reviewed by maintainers.

Small contributions (e.g. minor bug fixes) can be submitted as pull requests directly.

Larger contributions like feature additions should be preceded by a [Change Proposal](#rfcs--change-proposals) to allow maintainers and other people to discuss and decide if it's a good idea or not.

## Useful documentation from github.com
- [About pull requests](https://docs.github.com/en/github/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests)
- [About forks](https://docs.github.com/en/github/collaborating-with-pull-requests/working-with-forks/about-forks)
- [Creating a pull request from a fork](https://docs.github.com/en/github/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request-from-a-fork)

## Other things you'll need
- [git](https://git-scm.com/)

## Making a pull request
The basic procedure to create a pull request is:
1. [Fork the repository on GitHub](https://github.com/pmmp/PocketMine-MP/fork). This gives you your own copy of the repository to make changes to.
2. Create a branch on your fork for your changes.
3. Make the changes you want to make on this branch.
4. You can then make a [pull request](https://github.com/pmmp/PocketMine-MP/pull/new) to the project.

## Pull request reviews
Pull requests will be reviewed by maintainers when they are available.
Note that there might be a long wait time before a reviewer looks at your PR.

Depending on the changes, maintainers might ask you to make changes to the PR to fix problems or to improve the code.
**Do not delete your fork** while your pull request remains open, otherwise you won't be able to make any requested changes and the PR will end up being declined.

### Requirements
The following are required as a minimum for pull requests. PRs that don't meet these requirements will be declined unless updated to meet them.

#### Licensing
PocketMine-MP is licensed under [LGPLv3 license](LICENSE).
By proposing a pull request, you agree to your code being distributed within PocketMine-MP under the same license.
If you take code from other projects, that code MUST be licensed under an LGPL-compatible license.

#### PRs should be about exactly ONE thing
If you want to make multiple changes, those changes should each be contributed as separate pull requests. **DO NOT** mix unrelated changes.

#### PRs must not include unnecessary/unrelated changes
Do not include changes which aren't strictly necessary. This makes it harder to review a PR, because the code diff becomes larger and harder to review.
This means:
- don't reformat or rearrange existing code
- don't change things that aren't related to the PR's objective
- don't rewrite existing code just to make it "look nicer"
- don't change PhpDocs to native types in code you didn't write

#### Tests must be provided
Where possible, PHPUnit tests should be written for new or changed code.
If that's not possible (e.g. for in-game functionality), the code must be tested manually and details of the tests done must be provided.
**Simply saying "Tested" is not acceptable** and will lead to your PR being declined.

#### Comments and documentation must be written in American English
English is the shared languages of all current maintainers.

#### Code must be in the PocketMine-MP style
It's your responsibility to ensure your code matches the formatting and styling of the rest of the code.
If you use PhpStorm, a `Project` code style is provided, which you can use to automatically format new code.
You can also use [`php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to format your code.

### Recommendations

- **Do not edit code directly on github.com.** We recommend learning how to use [`git`](https://git-scm.com). `git` allows you to "clone" a repository onto your computer, so that you can make changes using an IDE.
- **Use an IDE, not a text editor.** We recommend PhpStorm or VSCode.
- **Create a new branch on your fork for each pull request.** This allows you to use the same fork to make multiple pull requests at the same time.
- **Use descriptive commit titles.** You can see an example [here](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html).
- **Do not include multiple unrelated changes in one commit.** An atomic style for commits is preferred - this means that changes included in a commit should be part of a single distinct change set. See [this link](https://www.freshconsulting.com/atomic-commits/) for more information on atomic commits. See the [documentation on `git add`](https://git-scm.com/docs/git-add) for information on how to isolate local changes for committing.
- **Your pull request will be checked and discussed in due time.** Since the team is scattered all around the world, your PR may not receive any attention for some time.
- **Do not make large pull requests without an RFC.** Large changes should be discussed beforehand using the [RFC / Change Proposal](#rfcs--change-proposals) process. Large changes are much harder to review and are more likely to be declined if maintainers don't have a good idea what you're trying to do in advance.
- **Do not copy-paste code**. There are potential license issues implicit with copy-pasting, and copy-paste usually indicates a lack of understanding of the actual code. Copy-pasted code is obvious a mile off and **any PR like this is likely to be closed**. If you want to use somebody else's code from a Git repository, **use [GIT's cherry-pick feature](https://git-scm.com/docs/git-cherry-pick)** to cherry-pick the commit.


**Thanks for contributing to PocketMine-MP!**

## RFCs / Change Proposals
Change Proposals are issues or discussions which describe a new feature proposal or behavioural change.
They are used to get feedback from maintainers and the community about an idea for a change, to decide whether or not it's a good idea.

### Submitting an RFC
RFCs should be submitted using Issues or Discussions.
RFCs _can_ be submitted as pull requests if you've already written the code, but this is not recommended, since it's not guaranteed that an RFC will pass, in which case your effort would be wasted.

RFCs should include the following:
- A summary of what you want to change
- Why you want to change it (e.g. what problems it solves)
- Alternative methods you've considered to solve the problem. This should include any possible ways that what you want can be done without the change.

### Voting on RFCs
Community members can vote on RFCs. This gives maintainers an idea of how popular the idea is.
Votes can be cast using :+1: and :-1: reactions.

**Please don't downvote without providing a reason why!**

### Implementing RFCs
Anyone can write the code to implement an RFC, and submit a pull request for it. It doesn't have to be the RFC author.

Implementations should be submitted as pull requests. The pull request description must include a link to the RFC.
