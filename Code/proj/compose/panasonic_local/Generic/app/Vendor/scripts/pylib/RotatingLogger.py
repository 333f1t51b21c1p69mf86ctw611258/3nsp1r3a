import logging
import logging.handlers as handlers
import os
from .appconfig import LOGGER_NAME
import time

class SizedTimedRotatingFileHandler(handlers.TimedRotatingFileHandler):
    """
    Handler for logging to a set of files, which switches from one file
    to the next when the current file reaches a certain size, or at certain
    timed intervals
    """
    def __init__(self, filename, mode='a', maxBytes=0, backupCount=0, encoding=None,
                 delay=0, when='h', interval=1, utc=False):
        # If rotation/rollover is wanted, it doesn't make sense to use another
        # mode. If for example 'w' were specified, then if there were multiple
        # runs of the calling application, the logs from previous runs would be
        # lost if the 'w' is respected, because the log file would be truncated
        # on each run.
        if maxBytes > 0:
            mode = 'a'
        handlers.TimedRotatingFileHandler.__init__(
            self, filename, when, interval, backupCount, encoding, delay, utc)
        self.maxBytes = maxBytes

    def shouldRollover(self, record):
        """
        Determine if rollover should occur.

        Basically, see if the supplied record would cause the file to exceed
        the size limit we have.
        """
        if self.stream is None:                 # delay was set...
            self.stream = self._open()
        if self.maxBytes > 0:                   # are we rolling over?
            msg = "%s\n" % self.format(record)
            self.stream.seek(0, 2)  #due to non-posix-compliant Windows feature
            if self.stream.tell() + len(msg) >= self.maxBytes:
                return 1
        t = int(time.time())
        if t >= self.rolloverAt:
            return 1
        return 0

def setup_logger():
    log_filename = os.getcwd() + '../../../tmp/logs/' + LOGGER_NAME + '.log'
    logger = logging.getLogger(LOGGER_NAME)
    #FORMAT = "%(asctime)-15s %(function)s %(message)s"
    #logger.setLevel(logging.INFO)
    #logging.basicConfig(format=FORMAT, filename=LOGGER_NAME)
    #logger = logging.getLogger(LOGGER_NAME)

    handler = SizedTimedRotatingFileHandler(
        log_filename, maxBytes=10000000, backupCount=5,
        when='s',interval=86400,
        # encoding='bz2',  # uncomment for bz2 compression
        )

    formatter = logging.Formatter("%(asctime)-15s %(function)s %(message)s")
    handler.setFormatter(formatter)
    logger.setLevel(logging.DEBUG)

    logger.addHandler(handler)

