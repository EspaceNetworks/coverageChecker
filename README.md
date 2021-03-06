# coverageChecker
Allows tools to conditionally allow standards to new code

[![Build Status](https://travis-ci.org/exussum12/coverageChecker.svg?branch=master)](https://travis-ci.org/exussum12/coverageChecker)
[![Coverage Status](https://coveralls.io/repos/github/exussum12/coverageChecker/badge.svg?branch=master)](https://coveralls.io/github/exussum12/coverageChecker?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/exussum12/coverageChecker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/exussum12/coverageChecker/?branch=master)

Coverage checker allows new standards to be implemented incrementally, by only enforcing them on new / edited code.

Tools like phpcs and phpmd are an all or nothing approach, coverage checker allows this to work with the diff i.e. enforce all of the pull request / change request.

Also working with PHPunit to allow, for example 90% of new/edited code to be covered. which will increase the overall coverage over time.

# Usage

## Composer
With composer simply

    composer require exussum12/coverage-checker
    
then call the script you need

## Manually
Clone this repository somewhere your your build plan can be accessed, composer install is prefered but there is a non composer class loader which will be used if composer is not installed.
Then call the script you need


# Scripts

First of all a diff is needed

    git diff origin/master...branch > /tmp/diff.txt
or 

    git diff origin/master...HEAD > /tmp/diff.txt
assuming your on the branch currently

or

    git diff $(git merge-base origin/master...HEAD) > /tmp/diff.txt
    
Assuming your on a branch with no local changes (ie all changes are committed) 

This diff will be used in all of the examples

the 3 dots between gets all changes on branch (ie from the branch point) and not from the current master.

From the git diff manual 

       --ignore-space-at-eol
           Ignore changes in whitespace at EOL.

       -b, --ignore-space-change
           Ignore changes in amount of whitespace. This ignores whitespace at
           line end, and considers all other sequences of one or more
           whitespace characters to be equivalent.

       -w, --ignore-all-space
           Ignore whitespace when comparing lines. This ignores differences
           even if one line has whitespace where the other line has none.

       --ignore-blank-lines
           Ignore changes whose lines are all blank.

These options can be useful to not force indented lines to be re written (which can be more risky!)
Using these whitespace options with phpcs can lead to errors being introduced as whitespace can cause phpcs errors.
Use with caution!

exit code 0 and no output indicates success

## phpunit

    php vendor/bin/diffFilter --phpunit /tmp/diff.txt report/coverage.xml  90
    
Will fail (exit status 2) if less than 90% of the code committed is covered by a test.
This requires phpunit to be run on the branch first!

Note: This works for all clover output!

## phpcs

All of the commands can read from stdin with placeholder `-`

    phpcs --report=json | php vendor/bin/diffFilter --phpcs /tmp/diff.txt -
    
phpcs can be run with any options you normally have for example `--standard=PSR2`

This will exit with code 2 if any of the new/edited code fails the code standards check. The output is kept so you can see what the offending lines are and what the error is.

Strict mode turns warning to errors


## phpmd

All of the commands can read from stdin with placeholder `-`

    phpmd src/ xml cleancode | php vendor/bin/diffFilter --phpmd /tmp/diff.txt -
    
phpmd can be run with any options you normally have for example `cleancode,codesize,controversial`

phpmd also has a strict mode `--phpmdstrict` instead of `--phpmd` which reports an error multiple times for each line which is not standard.
The normal mode reports the error once, eg a class has too many functions, strict mode reports this violation on each line. This makes old non conforming files easier to deal with as refactoring can be risky also.

This will exit with code 2 if any of the new/edited code fails the code standards check. The output is kept so you can see what the offending lines are and what the error is.


# Running in information mode
Simply pass the 3rd argument in as 0, this will give output showing failed lines but will not fail the build


# Why not run the auto fixers
Auto fixers do exist for some of these tools, but on larger code bases there are many instances where these can not be auto fixed. CoverageChecker allows to go to these new standards in the most used parts of the code by enforcing all changes to comply to the new standards

# What is a diff filtered test

A diff filtered test is a test where the execution and diffence (diff) is used from a known point.
This information can be used to only run the tests which have been changed. Saving in many cases minutes running tests.

A good workflow is to branch, run the tests with `--coverage-php=php-coverage.php`  and then when running your tests run `git diff origin/master... > diff.txt && ./composer/bin/phpunit`

This saves the coverage information in the first step which the diff then filters to runnable tests.

This one time effort saves running unnecessary tests on each run, tests for which the code has not changed.


# Running PHPUnit diff filtered tests

Adding the following code to your phpunit.xml, and adding a diff and the phpunit coverage output in php format (`--coverage-php`) will run only the tests necessary for the change 


    <listeners>
      <listener class="exussum12\CoverageChecker\DiffFilter" >
          <arguments>
              <string>php-coverage.php</string>
              <string>diff.txt</string>
          </arguments>
      </listener>
    </listeners>

