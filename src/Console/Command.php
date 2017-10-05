<?php

namespace Meat\Cli\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

trait Command
{
    /**
     * Run the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        return parent::run(
            $this->input = $input, $this->output = new OutputStyle($input, $output)
        );
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return (int) $this->fire();
    }

    /**
     * Get an argument from the input.
     *
     * @param  string  $key
     * @return string
     */
    public function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Get an option from the input.
     *
     * @param  string  $key
     * @return string
     */
    public function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * @param $question
     * @param bool $default
     *
     * @return bool
     */
    public function confirm($question, $default = true)
    {
        $helper = $this->getHelper('question');
        $question = '<comment>'.$question.'</comment>';
        $question = new ConfirmationQuestion($question, $default);

        return $helper->ask($this->input, $this->output, $question, '/^(y|j)/i');
    }

    public function choice($question, $default = null, $options = null, $errorMessage = '')
    {
        $question = '<comment>'.$question.'</comment>';
        $question = new ChoiceQuestion($question, $options, $default);


        $question->setErrorMessage('Invalid input');

        return $this->getHelperSet()->get('question')->ask($this->input, $this->output, $question);
    }

    /**
     * Ask the user the given question.
     *
     * @param  string  $question
     * @return string
     */
    public function ask($question, $default = null, $autocomplete = null)
    {
        $question = '<comment>'.$question.'</comment>';
        $question = new Question($question, $default);

        if ($autocomplete) {
            $question->setAutocompleterValues($autocomplete);
        }
        return $this->getHelperSet()->get('question')->ask($this->input, $this->output, $question);
    }

    /**
     * Confirm the operation with the user.
     *
     * @param  string  $task
     * @param  string  $question
     * @return bool
     */
    public function confirmTaskWithUser($task, $question)
    {
        $question = $question === true ? 'Are you sure you want to run the ['.$task.'] task?' : (string) $question;

        $question = '<comment>'.$question.' [y/N]:</comment> ';

        $question = new ConfirmationQuestion($question, false);

        return $this->getHelperSet()->get('question')->ask($this->input, $this->output, $question);
    }

    /**
     * Ask the user the given secret question.
     *
     * @param  string  $question
     * @return string
     */
    public function secret($question)
    {
        $question = '<comment>'.$question.'</comment> ';

        $question = new Question($question);
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        return $this->getHelperSet()->get('question')->ask($this->input, $this->output, $question);
    }

    public function line($line)
    {
        return $this->output->writeln($line);
    }

    public function info($line)
    {
        return $this->line('<info>' . $line . '</info>');
    }

    public function runProcess($command, $print_output = true, $timeout = null)
    {
        if ($print_output == null) {
            $print_output = $this->option('verbose');
        }
        $process = new Process($command);
        $process->setTimeout($timeout);
        if ($print_output) {
            return $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });
        }
        $process->run();
        return $process;
    }
}