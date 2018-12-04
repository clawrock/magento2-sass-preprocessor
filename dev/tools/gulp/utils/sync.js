import browserSync from 'browser-sync';
import gutil from 'gulp-util';
import chalk from 'chalk';
import { argv } from 'yargs';

const bs = browserSync.create('magento2');

export function initSync(options = {}) {
    if (!argv.proxy) {
        gutil.log(chalk.yellow('BrowserSync is disabled, please specify proxy argument.'));

        return;
    }

    const config = Object.assign(options, {
        proxy: argv.proxy
    });

    bs.init(config);
}

export function sync(stream) {
    return stream.pipe(bs.stream());
}
