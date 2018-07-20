#!/bin/bash
#2agl_cat_4_7_18.reg
#3FGL_gll_psc_v14.reg
#3FGL_gll_psc_v14_ell.reg

cd ..
cd sources
mkdir -p tmp


php mergeRegionCatalogs.php /ANALYSIS3/catalogs/3FGL_gll_psc_v14.reg /ANALYSIS3/catalogs/3FGL_gll_psc_v14_ell.reg


rm -r tmp
cd ..
cd tests
