<?php

use yii\db\Migration;

class m240000_000001_create_submission_tables extends Migration
{
    public function safeUp()
    {
        // Tabel pengajuan subtask
        $this->createTable('subtask_submissions', [
            'submission_id' => $this->primaryKey(),
            'subtask_id' => $this->integer()->notNull(),
            'submitted_by' => $this->integer()->notNull(),
            'reviewer_id' => $this->integer()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'submission_notes' => $this->text(),
            'review_notes' => $this->text(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'reviewed_at' => $this->dateTime()->null(),
        ]);

        $this->addForeignKey('fk_subtask_submission_subtask', 'subtask_submissions', 'subtask_id', 'subtasks', 'subtask_id', 'CASCADE');
        $this->addForeignKey('fk_subtask_submission_submitter', 'subtask_submissions', 'submitted_by', 'users', 'user_id', 'CASCADE');
        $this->addForeignKey('fk_subtask_submission_reviewer', 'subtask_submissions', 'reviewer_id', 'users', 'user_id', 'CASCADE');

        // Tabel pengajuan card
        $this->createTable('card_submissions', [
            'submission_id' => $this->primaryKey(),
            'card_id' => $this->integer()->notNull(),
            'submitted_by' => $this->integer()->notNull(),
            'reviewer_id' => $this->integer()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'submission_notes' => $this->text(),
            'review_notes' => $this->text(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'reviewed_at' => $this->dateTime()->null(),
        ]);

        $this->addForeignKey('fk_card_submission_card', 'card_submissions', 'card_id', 'cards', 'card_id', 'CASCADE');
        $this->addForeignKey('fk_card_submission_submitter', 'card_submissions', 'submitted_by', 'users', 'user_id', 'CASCADE');
        $this->addForeignKey('fk_card_submission_reviewer', 'card_submissions', 'reviewer_id', 'users', 'user_id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_subtask_submission_subtask', 'subtask_submissions');
        $this->dropForeignKey('fk_subtask_submission_submitter', 'subtask_submissions');
        $this->dropForeignKey('fk_subtask_submission_reviewer', 'subtask_submissions');
        $this->dropTable('subtask_submissions');

        $this->dropForeignKey('fk_card_submission_card', 'card_submissions');
        $this->dropForeignKey('fk_card_submission_submitter', 'card_submissions');
        $this->dropForeignKey('fk_card_submission_reviewer', 'card_submissions');
        $this->dropTable('card_submissions');
    }
}