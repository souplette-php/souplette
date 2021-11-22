import {promisify} from 'util'
import child_process from 'child_process'

interface Output {
  stdout: string
  stderr: string
}

const trimmed = ({stdout, stderr}: Output) => ({
  stdout: stdout.trim(),
  stderr: stderr.trim(),
})

const execFilePromise = promisify(child_process.execFile)
type ExecFileParams = Parameters<typeof execFilePromise>

export type ExecFileOptions = ExecFileParams[2]
type ExecFile = (cmd: string, args?: string[], options?: ExecFileOptions) => Promise<Output>

export const execFile: ExecFile = async (cmd, args = [], options = {}) => {
  const result = await execFilePromise(cmd, args, {...options, encoding: 'utf8'})
  return trimmed(result)
}
