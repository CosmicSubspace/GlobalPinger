# Global Pinger
A simple webpage for testing how crappy your ISP's international internet connection is.

## Installing
simply put this in a php and python-enabled server and then execute `globalping.py`.
The python script will now run every hour or so and collect ping data, which can be viewed in `index.php`.

## Configuration
The `config.json` file has all the options you can tweak. This file is read by both the python and php scripts and thus controls both the ping procedure and presentation.

Most options are self-explanatory, but here are some that needs more explanation:

  * `SleepDuration` controls how much seconds the script will wait BETWEEN running the ping procedure.
  * `PageHeaderMessage` will be inserted at the header of the page. Put whatever HTML you want in here. I just listed my location and ISP.
  * `Filename`s in `TargetList` must all be unique.
