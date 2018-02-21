from xlrd import open_workbook
import string
import sys
import re
import logging

from . import RotatingLogger
from .appconfig import LOGGER_NAME

RotatingLogger.setup_logger()
logger = logging.getLogger(LOGGER_NAME)

class ExcelHelper:
    def __init__(self):
        pass

    def readXls(self, filename, tablename, columns, rowFrom=1):
        wb = open_workbook(filename)
        rowsToSave = []

        for s in wb.sheets():
            #print 'Sheet:',s.name
            first = True
            for row in range(rowFrom, s.nrows):
                nameToAdd = {}
                first = False
                col_idx = 0
                for colname in columns:
                    val = s.cell(row, col_idx).value
                    #print col, val.value
                    nameToAdd[colname] = val
                    col_idx += 1
                rowsToSave.append(nameToAdd)

        return rowsToSave

    def _briodetype_to_mysqltype(self, kv):
        # FIXME
        #  varchar string length hardcoded
        #  should read from configdb
        type_map = {
            'string'    : 'varchar(30)',
            'decimal2'  : 'float',
            'date'      : 'timestamp'
        }
        ret = {}
        for k in kv:
            ret[k] = type_map[kv[k]]

        return ret

    def read_briode_schema(self, filename):
        """
        returns {(type, colname)... } map
        """
        wb = open_workbook(filename)
        type_cols = {}

        col_def_ptn = '[^:]*:([^:]*):([^:]*):.*'
        for s in wb.sheets():
            for row in range(0, s.nrows):
                for col in range(0, s.ncols):
                    try:
                        val = s.cell(row, col).value
                    except IndexError:
                        # col read failed at far right, bail out
                        continue
                    #print('cell val={}'.format(val))
                    matched = re.match(col_def_ptn, str(val))
                    if matched:
                        #print('def found:{},{}'.format(matched.group(1), matched.group(2)))
                        type_cols[matched.group(1)] = matched.group(2)

        return self._briodetype_to_mysqltype(type_cols)
