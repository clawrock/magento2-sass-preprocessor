import browserSync from 'browser-sync';
import log from 'fancy-log';
import chalk from 'chalk';
import { argv } from 'yargs';
import bsConfig from '../config/browser-sync';

const bs = browserSync.create('magento2');

export function isSyncEnabled() {
    return !!argv.proxy;
}

export function initSync() {
    if (!argv.proxy) {
        log.info(chalk.yellow('BrowserSync is disabled, please specify proxy argument.'));

        return;
    }

    const domain = `.${argv.proxy.split('//')[1]}`;

    const config = Object.assign(bsConfig, {
        rewriteRules: [{
            match: domain,
            replace: ""
        }],
        proxy: argv.proxy
    });

    bs.init(config);
}

export function syncStream(stream) {
    return stream.pipe(bs.stream());
}

export function syncReload() {
    return bs.reload();
}
