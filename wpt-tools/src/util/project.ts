import {execFile} from './system'

let projectRoot: string

export default {
  root: async () => {
    if (!projectRoot) {
      const {stdout} = await execFile('git', ['-C', __dirname, 'rev-parse', '--show-toplevel'])
      projectRoot = stdout
    }
    return projectRoot
  }
}
