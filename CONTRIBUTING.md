# Contributing Guidelines

Contributions in any form are welcome. 

## Reporting bugs

If you'd like to report a bug you may open an issue on github.

## Contributing Code

When contributing code, you are doing this under the terms of the
[GNU Lesser General Public License Version 3](http://www.gnu.org/licenses/lgpl-3.0.html) (LGPL) and you
agree that your contribution will fall under this license as well. You also agree that you
will not contribute code that is copyright of a third party or infringes the LGPL of this project.

To contribute code, fork this repository on github, make your changes and open a pull request.
When your change fixes a bug, open the pull request against the `master` branch. For all other changes
the pull request must be targeted to the `develop` branch.

Make sure the following requirements are met:

 - The changes must pass CI which includes:
    - Unit tests
    - Infection tests
    - Static analysis
    - Coding style checks
 - Your changes are covered by tests.
 - Update the changelog (CHANGELOG.md) accordingly. Changes that do not
   affect library users (i.e. CI config) may be omitted.
 - When you submit a feature describe what it does and what
   the use case is. State an example for better understanding
   if necessary.
 - Provide documentation for new features
  
Please note that we may decide to reject your contribution, especially when
these requirements are not met.
