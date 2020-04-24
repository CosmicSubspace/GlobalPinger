# Global Pinger
A simple webapp for testing how crappy your ISP's international internet connection is.

## Installing
simply put this in a php and python-enabled server and then execute `globalping.py`

## Configuration
The `config.json` file has all the options you can tweak. This file is read by both the python and php scripts and thus controls both the ping procedure and presentation.
Most options are self-explanatory.
-- `SleepDuration` controls how much seconds the script will wait BETWEEN running the ping procedure.
-- `Filename`s in the `TargetList` must all be unique.
