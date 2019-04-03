import ThemeRegistry from './theme-registry';
import fs from 'fs';

class AssetDeployer {
    constructor(theme) {
        this.theme = (new ThemeRegistry()).getTheme(theme);
        this.magentoImport = {};
        this.resolveMagentoImport();
    }

    resolveSymlinkPath(sourceFile) {
        const destinationParts = sourceFile.split('/').splice(5).filter(part => part !== 'web');
        destinationParts.pop();

        return `${this.theme.path}${destinationParts.join('/')}`;
    }

    isMagentoImportFile(path) {
        return Object.keys(this.magentoImport).some(file => {
            return this.magentoImport[file].some(pattern => {
                return path.includes(pattern);
            });
        });
    }

    resolveMagentoImport() {
        this.theme.sourceFiles.forEach((file) => {
            const data = fs.readFileSync(file, 'UTF-8');
            const importRe = new RegExp('\/\/@magento_import[^;]*', 'gm');
            const result = data.match(importRe);
            if (!result) {
                return;
            }
            result.forEach((line) => {
                const lineRe = new RegExp('[\'"](.*)[\'"]');
                const lineResult = line.match(lineRe);
                if (lineResult) {
                    if (this.magentoImport[file] === undefined) {
                        this.magentoImport[file] = [];
                    }
                    this.magentoImport[file].push(lineResult[1]);
                }
            });
        });
    }
}

export default AssetDeployer;
