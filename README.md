# Delay step (Life cycle step)

This action step will simply act as a time delay between other action steps in the lifecycle workflow.
It will start as soon as a workflow enters the delay step and will end after the specified time.

Installation
============
This is an admin plugin and should go into ``admin/tool/lcdelaystep``.

Dependencies
============
This plugin depends on the following plugins:
* Life cycle: https://moodle.org/plugins/view/tool_lifecycle.
* The following refactoring is accepted https://github.com/learnweb/moodle-tool_lifecycle/pull/189

Configuration
============
• Delay time to be applied from the time the step is entered until it can progress to the next step
A value is entered specifying the number of weeks, days, hours, minutes or seconds.
