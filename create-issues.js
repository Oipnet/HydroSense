#!/usr/bin/env node

/**
 * Script to create GitHub issues for the HydroSense project
 * 
 * Usage:
 *   GITHUB_TOKEN=your_token node create-issues.js
 * 
 * The script will:
 * 1. Read issue data from issues-data.json
 * 2. Create labels if they don't exist
 * 3. Create all 24 issues with proper labels and formatting
 */

const fs = require('fs');
const https = require('https');

const REPO_OWNER = 'Oipnet';
const REPO_NAME = 'HydroSense';
const GITHUB_TOKEN = process.env.GITHUB_TOKEN;

if (!GITHUB_TOKEN) {
  console.error('❌ Error: GITHUB_TOKEN environment variable is required');
  console.error('Usage: GITHUB_TOKEN=your_token node create-issues.js');
  process.exit(1);
}

// Read issues data
const issuesData = JSON.parse(fs.readFileSync('issues-data.json', 'utf8'));

// GitHub API request helper
function githubRequest(method, path, data = null) {
  return new Promise((resolve, reject) => {
    const options = {
      hostname: 'api.github.com',
      port: 443,
      path: path,
      method: method,
      headers: {
        'User-Agent': 'HydroSense-Issue-Creator',
        'Authorization': `token ${GITHUB_TOKEN}`,
        'Accept': 'application/vnd.github.v3+json',
        'Content-Type': 'application/json',
      }
    };

    const req = https.request(options, (res) => {
      let responseData = '';

      res.on('data', (chunk) => {
        responseData += chunk;
      });

      res.on('end', () => {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          resolve(JSON.parse(responseData || '{}'));
        } else {
          reject(new Error(`HTTP ${res.statusCode}: ${responseData}`));
        }
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    if (data) {
      req.write(JSON.stringify(data));
    }

    req.end();
  });
}

// Create a label if it doesn't exist
async function createLabelIfNeeded(labelName) {
  try {
    // Try to get the label first
    await githubRequest('GET', `/repos/${REPO_OWNER}/${REPO_NAME}/labels/${encodeURIComponent(labelName)}`);
    console.log(`  ✓ Label "${labelName}" already exists`);
  } catch (error) {
    // If label doesn't exist, create it
    if (error.message.includes('404')) {
      const labelColors = {
        'epic:setup': '0E8A16',
        'epic:backend': '1D76DB',
        'epic:frontend': 'FBCA04',
        'epic:infra': 'D93F0B',
        'epic:ia': '8B4789',
        'backend': '0075CA',
        'frontend': 'F9D0C4',
        'infra': 'E99695',
        'ia': 'C5DEF5'
      };

      const color = labelColors[labelName] || 'EDEDED';
      
      try {
        await githubRequest('POST', `/repos/${REPO_OWNER}/${REPO_NAME}/labels`, {
          name: labelName,
          color: color,
          description: `Label for ${labelName}`
        });
        console.log(`  ✓ Created label "${labelName}"`);
      } catch (createError) {
        console.error(`  ✗ Failed to create label "${labelName}":`, createError.message);
      }
    } else {
      console.error(`  ✗ Error checking label "${labelName}":`, error.message);
    }
  }
}

// Create an issue
async function createIssue(issueData) {
  try {
    const result = await githubRequest('POST', `/repos/${REPO_OWNER}/${REPO_NAME}/issues`, {
      title: issueData.title,
      body: issueData.body,
      labels: issueData.labels
    });
    console.log(`  ✓ Created issue #${result.number}: ${issueData.title}`);
    return result;
  } catch (error) {
    console.error(`  ✗ Failed to create issue "${issueData.title}":`, error.message);
    return null;
  }
}

// Main function
async function main() {
  console.log('🚀 Starting GitHub issues creation for HydroSense\n');
  console.log(`Repository: ${REPO_OWNER}/${REPO_NAME}`);
  console.log(`Total issues to create: ${issuesData.issues.length}\n`);

  // Step 1: Create all necessary labels
  console.log('📝 Step 1: Creating labels...');
  const allLabels = new Set();
  issuesData.issues.forEach(issue => {
    issue.labels.forEach(label => allLabels.add(label));
  });

  for (const label of allLabels) {
    await createLabelIfNeeded(label);
    // Small delay to avoid rate limiting
    await new Promise(resolve => setTimeout(resolve, 100));
  }

  console.log('\n✅ Labels creation completed\n');

  // Step 2: Create all issues
  console.log('📋 Step 2: Creating issues...\n');
  
  let successCount = 0;
  let failCount = 0;

  for (const issueData of issuesData.issues) {
    console.log(`Creating issue ${issueData.number}/24: ${issueData.title}`);
    const result = await createIssue(issueData);
    if (result) {
      successCount++;
    } else {
      failCount++;
    }
    // Small delay to avoid rate limiting
    await new Promise(resolve => setTimeout(resolve, 500));
  }

  console.log('\n' + '='.repeat(60));
  console.log('📊 Summary:');
  console.log(`  ✓ Successfully created: ${successCount} issues`);
  if (failCount > 0) {
    console.log(`  ✗ Failed: ${failCount} issues`);
  }
  console.log('='.repeat(60));
  console.log('\n✨ Done! Check your issues at:');
  console.log(`   https://github.com/${REPO_OWNER}/${REPO_NAME}/issues\n`);
}

// Run the script
main().catch(error => {
  console.error('\n❌ Script failed:', error.message);
  process.exit(1);
});
