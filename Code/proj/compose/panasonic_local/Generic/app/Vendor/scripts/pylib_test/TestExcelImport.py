from unittest import TestCase
import os
import json
import copy
from pylib import ExcelHelper
from pylib import DBMaint
from pylib import DBHelper

EXCEL_FORMAT_DIR=os.environ['EXCEL_FORMAT_DIR']
EXCEL_IMPORT_DIR=os.environ['EXCEL_IMPORT_DIR']

class TestExcelImport(TestCase):
    def setUp(self):
        self.plugin_name = 'AppTest'
        self.json_app_file = '{}/appconfig/{}.json'.format(os.getcwd(), self.plugin_name)
        self.json = self._load_json()

    def tearDown(self):
        pass

    def _load_json(self):
        ret = {}
        with open(self.json_app_file) as f:
            ret.update(json.load(f))
        return ret

    def test_excel_import(self):
        global EXCEL_FORMAT_DIR, EXCEL_IMPORT_DIR
        excel_format_fullpath = '{}/{}.xlsx'.format(EXCEL_FORMAT_DIR, self.plugin_name)

        # read Excel schema and create table
        eh = ExcelHelper.ExcelHelper()

        schema = eh.read_briode_schema(excel_format_fullpath)
        with DBMaint.DBMaint() as db:
            tblname = 'attr{}s'.format(self.plugin_name.lower())
            db.create_table(tblname, schema)

        # load values from Excel 
        excel_import_fullpath = '{}/{}_data.xlsx'.format(EXCEL_IMPORT_DIR, self.plugin_name)
        tblname = 'attr{}s'.format(self.plugin_name.lower())
        header = key_col_names = cols = self.json["db_import"]['columns']
        rows = eh.readXls(excel_import_fullpath, tblname, cols)
        print(key_col_names)
        DBHelper.updateDB(tblname, header, rows, key_col_names)

if __name__ == '__main__':
    unittest.main()

