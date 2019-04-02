import gulp from 'gulp';
import filesRouter from '../../grunt/tools/files-router';

export default function(task, callback, deps = []) {
    filesRouter.set('themes', './dev/tools/grunt/configs/themes');
    const themes = filesRouter.get('themes');

    Object.keys(themes).forEach(theme => {
        const tasks = [...deps.map(el => `${el}:${theme}`)];
        if (typeof callback === 'function') {
            tasks.push(Object.defineProperty((done) => {
                return callback(done, theme);
            }, 'name', { value: task }));
        }
        gulp.task(`${task}:${theme}`, gulp.series.apply(this, tasks));
    });
}
