import gulp from 'gulp';
import AssetDeployer from '../utils/asset-deployer';
import ThemeRegistry from '../utils/theme-registry';
import { initSync, syncReload, isSyncEnabled } from '../utils/sync';
import path from 'path';
import log from 'fancy-log';
import chalk from 'chalk';
import del from 'del';
import { argv } from 'yargs';

function relativizePath(absolutePath) {
    return path.relative(process.cwd(), absolutePath);
}

export default function (done, theme) {
    initSync();
    const assetDeployer = new AssetDeployer(theme);
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);
    const mainWatcher = gulp.watch(`${themeConfig.path}**/*.${themeConfig.dsl}`, gulp.series([`${themeConfig.dsl}:${theme}`]))
        .on('change', path => {
            log.info(chalk.white(`File ${relativizePath(path)} was changed`));
        });

    gulp.watch(`${themeConfig.sourcePath}**/*.${themeConfig.dsl}`)
        .on('add', path => {
            if (assetDeployer.isMagentoImportFile(path)) {
                mainWatcher.unwatch(`${themeConfig.path}**/*.${themeConfig.dsl}`);
                log.info(chalk.white(`File ${relativizePath(path)} detected as @magento_import, deploying source theme...`));
                gulp.task(`exec:${theme}`)(() => {
                    mainWatcher.add(`${themeConfig.path}**/*.${themeConfig.dsl}`);
                    gulp.task(`${themeConfig.dsl}:${theme}`)();
                });
                return;
            }

            gulp.src(path).pipe(gulp.symlink(assetDeployer.resolveSymlinkPath(path)));
            log.info(chalk.white(`File ${relativizePath(path)} was created and linked pub`));
        }).on('unlink', path => {
            mainWatcher.unwatch(`${themeConfig.path}**/*.${themeConfig.dsl}`);
            del([assetDeployer.resolveSymlinkPath(path)]).then(() => {
                log.info(chalk.white(`File ${relativizePath(path)} was deleted`));
                if (assetDeployer.isMagentoImportFile(path)) {
                    log.info(chalk.white(`File ${relativizePath(path)} detected as @magento_import, deploying source theme...`));
                    gulp.task(`exec:${theme}`)(() => {
                        mainWatcher.add(`${themeConfig.path}**/*.${themeConfig.dsl}`);
                        gulp.task(`${themeConfig.dsl}:${theme}`)();
                    });
                    return;
                }
                mainWatcher.add(`${themeConfig.path}**/*.${themeConfig.dsl}`);
            });
        });

    const requireJsCallback = cb => {
        del([`${themeConfig.path}requirejs-config.js`]).then(() => {
            log.info(chalk.white(`Combined RequireJS configuration file removed from pub/static.`));
            cb();
        });
    };

    gulp.watch([
        'app/code/**/requirejs-config.js',
        `${themeConfig.sourcePath}**/requirejs-config.js`
    ], requireJsCallback);

    if (!isSyncEnabled()) {
        return;
    }

    const reload = cb => {
        syncReload();
        cb();
    };

    if (argv.phtml) {
        gulp.watch([
            'app/code/**/*.phtml',
            `${themeConfig.sourcePath}**/*.phtml`
        ], reload);
    }

    if (argv.js) {
        gulp.watch([
            'app/code/**/*.js',
            `${themeConfig.sourcePath}**/*.js`
        ], reload);
    }
}
