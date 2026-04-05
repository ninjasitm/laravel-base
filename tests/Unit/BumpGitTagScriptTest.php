<?php

use PHPUnit\Framework\TestCase;

class BumpGitTagScriptTest extends TestCase
{
    public function test_bump_git_tag_supports_override_release_notes_via_message_file(): void
    {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('--message-file)', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="$(cat "$1")"', $contents);
        $this->assertStringContainsString('--message-file requires a readable file path', $contents);
    }

    public function test_bump_git_tag_supports_override_release_notes_via_named_flag(): void
    {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('case "$1" in', $contents);
        $this->assertStringContainsString('--message)', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="${1:-$MASTER_COMMIT_MESSAGE}"', $contents);
    }

    public function test_bump_git_tag_uses_master_commit_message_for_release_notes(): void
    {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('TARGET_REF="${CI_COMMIT_SHA:-HEAD}"', $contents);
        $this->assertStringContainsString('MASTER_COMMIT_MESSAGE=$(git log --format=%B -n 1 "$TARGET_REF")', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="$MASTER_COMMIT_MESSAGE"', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="$MASTER_COMMIT_MESSAGE"', $contents);
        $this->assertStringContainsString('git tag -a "$NEW_TAG" -m "$RELEASE_NOTES" --force', $contents);
    }

    public function test_bump_git_tag_exits_on_command_failure(): void
    {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('set -euo pipefail', $contents);
    }

    public function test_bump_git_tag_resolves_commit_from_target_ref_instead_of_hardcoded_master(): void
    {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('GIT_COMMIT=$(git rev-parse "$TARGET_REF")', $contents);
        $this->assertStringNotContainsString('git rev-parse master', $contents);
    }

    public function test_bump_git_tag_does_not_create_lightweight_tag_before_annotated_tag(): void
    {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringNotContainsString('git tag $NEW_TAG --force', $contents);
    }

    private function fileContents(string $relativePath): string
    {
        $path = dirname(__DIR__, 2).'/'.$relativePath;

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents, 'Failed to read '.$relativePath);

        return $contents;
    }
}
