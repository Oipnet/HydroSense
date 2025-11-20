#!/usr/bin/env python3

"""
Script to create GitHub issues for the HydroSense project

Usage:
    GITHUB_TOKEN=your_token python3 create-issues.py

The script will:
1. Read issue data from issues-data.json
2. Create labels if they don't exist
3. Create all 24 issues with proper labels and formatting
"""

import json
import os
import sys
import time
import urllib.request
import urllib.error

REPO_OWNER = 'Oipnet'
REPO_NAME = 'HydroSense'
GITHUB_TOKEN = os.environ.get('GITHUB_TOKEN')

# Label colors
LABEL_COLORS = {
    'epic:setup': '0E8A16',
    'epic:backend': '1D76DB',
    'epic:frontend': 'FBCA04',
    'epic:infra': 'D93F0B',
    'epic:ia': '8B4789',
    'backend': '0075CA',
    'frontend': 'F9D0C4',
    'infra': 'E99695',
    'ia': 'C5DEF5'
}


def github_request(method, path, data=None):
    """Make a request to the GitHub API"""
    url = f'https://api.github.com{path}'
    headers = {
        'User-Agent': 'HydroSense-Issue-Creator',
        'Authorization': f'token {GITHUB_TOKEN}',
        'Accept': 'application/vnd.github.v3+json',
        'Content-Type': 'application/json',
    }
    
    request_data = json.dumps(data).encode('utf-8') if data else None
    req = urllib.request.Request(url, data=request_data, headers=headers, method=method)
    
    try:
        with urllib.request.urlopen(req) as response:
            return json.loads(response.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        error_body = e.read().decode('utf-8')
        raise Exception(f'HTTP {e.code}: {error_body}')


def create_label_if_needed(label_name):
    """Create a label if it doesn't exist"""
    try:
        # Try to get the label first
        github_request('GET', f'/repos/{REPO_OWNER}/{REPO_NAME}/labels/{urllib.parse.quote(label_name)}')
        print(f'  ✓ Label "{label_name}" already exists')
    except Exception as e:
        # If label doesn't exist, create it
        if '404' in str(e):
            color = LABEL_COLORS.get(label_name, 'EDEDED')
            try:
                github_request('POST', f'/repos/{REPO_OWNER}/{REPO_NAME}/labels', {
                    'name': label_name,
                    'color': color,
                    'description': f'Label for {label_name}'
                })
                print(f'  ✓ Created label "{label_name}"')
            except Exception as create_error:
                print(f'  ✗ Failed to create label "{label_name}": {create_error}')
        else:
            print(f'  ✗ Error checking label "{label_name}": {e}')


def create_issue(issue_data):
    """Create an issue"""
    try:
        result = github_request('POST', f'/repos/{REPO_OWNER}/{REPO_NAME}/issues', {
            'title': issue_data['title'],
            'body': issue_data['body'],
            'labels': issue_data['labels']
        })
        print(f'  ✓ Created issue #{result["number"]}: {issue_data["title"]}')
        return result
    except Exception as e:
        print(f'  ✗ Failed to create issue "{issue_data["title"]}": {e}')
        raise


def main():
    """Main function"""
    if not GITHUB_TOKEN:
        print('❌ Error: GITHUB_TOKEN environment variable is required')
        print('Usage: GITHUB_TOKEN=your_token python3 create-issues.py')
        sys.exit(1)
    
    print('🚀 Starting GitHub issues creation for HydroSense\n')
    print(f'Repository: {REPO_OWNER}/{REPO_NAME}')
    
    # Load issues data
    try:
        with open('issues-data.json', 'r', encoding='utf-8') as f:
            issues_data = json.load(f)
    except FileNotFoundError:
        print('❌ Error: issues-data.json not found')
        sys.exit(1)
    
    print(f'Total issues to create: {len(issues_data["issues"])}\n')
    
    # Step 1: Create all necessary labels
    print('📝 Step 1: Creating labels...')
    all_labels = set()
    for issue in issues_data['issues']:
        all_labels.update(issue['labels'])
    
    for label in sorted(all_labels):
        create_label_if_needed(label)
        time.sleep(0.1)  # Small delay to avoid rate limiting
    
    print('\n✅ Labels creation completed\n')
    
    # Step 2: Create all issues
    print('📋 Step 2: Creating issues...\n')
    
    success_count = 0
    fail_count = 0
    
    for issue_data in issues_data['issues']:
        try:
            print(f'Creating issue {issue_data["number"]}/24: {issue_data["title"]}')
            create_issue(issue_data)
            success_count += 1
            time.sleep(0.5)  # Small delay to avoid rate limiting
        except Exception:
            fail_count += 1
            print(f'Failed to create issue {issue_data["number"]}')
    
    print('\n' + '=' * 60)
    print('📊 Summary:')
    print(f'  ✓ Successfully created: {success_count} issues')
    if fail_count > 0:
        print(f'  ✗ Failed: {fail_count} issues')
    print('=' * 60)
    print('\n✨ Done! Check your issues at:')
    print(f'   https://github.com/{REPO_OWNER}/{REPO_NAME}/issues\n')


if __name__ == '__main__':
    main()
