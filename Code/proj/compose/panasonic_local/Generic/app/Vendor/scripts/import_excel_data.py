import json
import os
import sys
from pylib import ExcelHelper
from pylib import DBMaint
from pylib import DBHelper

EXCEL_IMPORT_DIR=os.environ['EXCEL_IMPORT_DIR']

def load_json(plugin_name):
    json_file = '{}/appconfig/{}.json'.format(os.getcwd(), plugin_name)
    ret = {}
    with open(json_file) as f:
        ret = json.load(f)
    return ret

def import_excel(plugin_name):
    # read Excel schema and create table
    eh = ExcelHelper.ExcelHelper()

    json = load_json(plugin_name)

    # load values from Excel 
    excel_import_fullpath = '{}/{}_data.xlsx'.format(EXCEL_IMPORT_DIR, plugin_name)
    tblname = 'attr{}s'.format(plugin_name.lower())
    header = key_col_names = cols = json["db_import"]['columns']
    rows = eh.readXls(excel_import_fullpath, tblname, cols)
    DBHelper.updateDB(tblname, header, rows, key_col_names)

def usage(app_name):
    print('Usage: {} <plugin_name>'.format(app_name))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        usage()
        sys.exit(1)

    import_excel(sys.argv[1])

