import browserSync from 'browser-sync';
import log from 'fancy-log';
import chalk from 'chalk';
import { argv } from 'yargs';

const bs = browserSync.create('magento2');

export function initSync(options = {}) {
    if (!argv.proxy) {
        log.info(chalk.yellow('BrowserSync is disabled, please specify proxy argument.'));

        return;
    }

    const domain = `.${argv.proxy.split('//')[1]}`;

    const config = Object.assign(options, {
        rewriteRules: [{
            match: domain,
            replace: ""
        }],
        proxy: argv.proxy
    });

    bs.init(config);
}

export function sync(stream) {
    return stream.pipe(bs.stream());
}
