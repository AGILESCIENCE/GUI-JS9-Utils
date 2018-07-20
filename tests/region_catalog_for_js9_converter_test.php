#!/bin/bash

cd ..
cd sources
mkdir -p tmp


#php regionCatalogForJS9Converter.php 2agl_cat_4_7_18.reg 0 galactic 92.4053 -10.3623 10
#php regionCatalogForJS9Converter.php 3FGL_gll_psc_v14.reg 1 fk5 92.4053 -10.3623 10
#php regionCatalogForJS9Converter.php /ANALYSIS3/catalogs/3FGL_gll_psc_v14_ell.reg 1 fk5 92.4053 -10.3623 10
php regionCatalogForJS9Converter.php /opt/prod/js9Utils/tests/catalogs/3FGL_gll_psc_v14_reg_fermiCatalogsmerged.reg 1 fk5 92.4053 -10.3623 10

rm -r tmp
cd ..
cd tests
