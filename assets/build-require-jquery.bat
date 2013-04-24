cd .\modules\bazalt\
start build-bazalt-cms.bat
cd ..\..\
copy /Y .\modules\require\require.js /B + .\modules\jquery\jquery-1.9.1.min.js /B + .\modules\underscore\underscore-min.js /B + .\modules\angular\angular-loader.min.js /B + .\modules.js /B .\require-jquery.js
exit