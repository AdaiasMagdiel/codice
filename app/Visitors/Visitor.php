<?php

namespace App\Visitors;

use App\Ast;

interface Visitor
{
    public function visitAssignExpr(Ast\AssignExpr $expr);
    public function visitBinaryExpr(Ast\BinaryExpr $expr);
    public function visitBlock(Ast\Block $stmt);
    public function visitBoolLiteral(Ast\BoolLiteral $expr);
    public function visitCallExpr(Ast\CallExpr $expr);
    public function visitConstDeclExpr(Ast\ConstDeclExpr $expr);
    public function visitExprStatement(Ast\ExprStatement $expr);
    public function visitFloatLiteral(Ast\FloatLiteral $expr);
    public function visitForStatement(Ast\ForStatement $stmt);
    public function visitIdentifier(Ast\Identifier $expr);
    public function visitIfStatement(Ast\IfStatement $stmt);
    public function visitIntLiteral(Ast\IntLiteral $expr);
    public function visitNullLiteral(Ast\NullLiteral $expr);
    public function visitPostfixExpr(Ast\PostfixExpr $expr);
    public function visitProgram(Ast\Program $program);
    public function visitStringLiteral(Ast\StringLiteral $expr);
    public function visitUnaryExpr(Ast\UnaryExpr $expr);
    public function visitVarDeclExpr(Ast\VarDeclExpr $expr);
}
