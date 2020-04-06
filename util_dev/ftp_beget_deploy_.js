let util = require('util');
const path = require('path');
var FtpDeploy = require("ftp-deploy");
var ftpDeploy = new FtpDeploy();

(async () => {
        try {

                let config = {
                        user: 'tab4lioz_laracsv',
                        password: 'Bravo2Alpha1!',
                        port: 22,
                        host: 'tab4lioz.beget.tech',
                        localRoot: '/../tmp/',
                        remoteRoot: './laracsv/laracsv/tmp/',
                        include: ['*', '**/*'],
                        exclude: [
                                "tests/**",
                                ".vscode/**",
                                "vendor/**",
                                "config/**",
                                "laracsv.code-workspace",
                                "tmp/**",
                                "node_modules/**",
                                "public/hot/**",
                                "public/storage/**",
                                "storage/*.key",
                                "config/database.php",
                                "util_dev/**",
                                ".vscode/**",
                                ".env",
                                ".env.backup",
                                ".phpunit.result.cache",
                                "Homestead.json",
                                "Homestead.yaml",
                                "npm-debug.log",
                                "yarn-error.log",
                        ],
                        deleteRemote: false,

                };

                config = {
                        user: 'tab4lioz_laracsv',
                        password: 'Bravo2Alpha1!',
                        port: 22,
                        host: 'tab4lioz.beget.tech',
                        localRoot: '/../tmp/',
                        remoteRoot: 'laracsv/tmp/',
                        include: ['*', '**/*'],
                        exclude: [
                                "tests/**",
                                ".vscode/**",
                                "vendor/**",
                                "config/**",
                                "laracsv.code-workspace",
                                "tmp/**",
                                "node_modules/**",
                                "public/hot/**",
                                "public/storage/**",
                                "storage/*.key",
                                "config/database.php",
                                "util_dev/**",
                                ".vscode/**",
                                ".env",
                                ".env.backup",
                                ".phpunit.result.cache",
                                "Homestead.json",
                                "Homestead.yaml",
                                "npm-debug.log",
                                "yarn-error.log",
                        ],
                        deleteRemote: false,

                };
                
                ftpDeploy.on("uploading", function(data) {
                        console.log(data.totalFilesCount); // total file count being transferred
                        console.log(data.transferredFileCount); // number of files transferred
                        console.log(data.filename); // partial path with filename being uploaded
                    });

           
                let res = await ftpDeploy.deploy(config);
                console.log(util.inspect(res));
              
     
        } catch(e){
                console.log(e);
        }
})();


