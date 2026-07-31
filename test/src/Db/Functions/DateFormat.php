<?php

namespace CommonTest\Db\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;
use Override;

/**
 * The filters use MySQLs DATE_FORMAT, which the consuming application registers itself. The
 * tests only need the DQL to be parseable, the generated SQL is never executed.
 */
class DateFormat extends FunctionNode
{
	private Node $date;

	private Node $format;

	#[Override] public function parse(Parser $parser): void
	{
		$parser->match(TokenType::T_IDENTIFIER);
		$parser->match(TokenType::T_OPEN_PARENTHESIS);

		$this->date = $parser->ArithmeticPrimary();

		$parser->match(TokenType::T_COMMA);

		$this->format = $parser->StringPrimary();

		$parser->match(TokenType::T_CLOSE_PARENTHESIS);
	}

	#[Override] public function getSql(SqlWalker $sqlWalker): string
	{
		return sprintf(
			'DATE_FORMAT(%s, %s)',
			$this->date->dispatch($sqlWalker),
			$this->format->dispatch($sqlWalker)
		);
	}
}
