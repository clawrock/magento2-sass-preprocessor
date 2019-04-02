import browserSync from 'browser-sync';
import log from 'fancy-log';
import chalk from 'chalk';
import { argv } from 'yargs';

const bs = browserSync.create('magento2');

function argvSyncOptions() {
    const options = {};
    [
        'bs-ui',
        'bs-port',
        'bs-logLevel',
        'bs-logPrefix',
        'bs-logConnections',
        'bs-logFileChanges',
        'bs-open',
        'bs-browser',
        'bs-notify',
        'bs-scrollProportionally',
        'bs-scrollThrottle',
        'bs-reloadDelay',
        'bs-reloadDebounce',
        'bs-reloadThrottle',
        'bs-injectChanges',
        'bs-startPath'
    ].forEach(arg => {
        if (argv[arg] !== undefined) {
            options[arg.substring(3)] = argv[arg];
        }
    });

    return options;
}

export function isSyncEnabled() {
    return !!argv.proxy;
}

export function initSync(options = {}) {
    if (!argv.proxy) {
        log.info(chalk.yellow('BrowserSync is disabled, please specify proxy argument.'));

        return;
    }

    const domain = `.${argv.proxy.split('//')[1]}`;

    const config = Object.assign(argvSyncOptions(), options, {
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
