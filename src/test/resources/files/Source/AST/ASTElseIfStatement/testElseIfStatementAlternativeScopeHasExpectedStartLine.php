<?php
function testElseIfStatementAlternativeScopeHasExpectedStartLine($value)
{
    if ($value === 42):
        echo 'Yes', PHP_EOL;
    elseif
        ($value < 42)
            :
        echo 'No', PHP_EOL;
    endif
        ;
}

testElseIfStatementAlternativeScopeHasExpectedStartLine(23);