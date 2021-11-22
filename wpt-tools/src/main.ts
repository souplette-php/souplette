import yargs from 'yargs'
import {hideBin} from 'yargs/helpers'

yargs(hideBin(process.argv))
  .scriptName('wpt-tools')
  .commandDir('./commands', {extensions: ['js']})
  .demandCommand()
  .help()
  .wrap(yargs.terminalWidth())
  .completion()
  .argv
