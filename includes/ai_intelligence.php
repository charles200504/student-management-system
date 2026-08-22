<?php
declare(strict_types=1);

function generate_academic_ai_analysis(array $student, array $modules): array
{
    $gpa = (float)($student['gpa'] ?? 0.0);
    $totalCredits = 0;
    $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'F' => 0];

    foreach ($modules as $m) {
        $totalCredits += (int)($m['credits'] ?? 20);
        $grade = strtoupper(substr($m['grade'] ?? 'F', 0, 1));
        if (isset($gradeCounts[$grade])) {
            $gradeCounts[$grade]++;
        }
    }

    // 1. Predictive Degree Classification
    $predictedHonor = 'Third Class / Pass';
    $riskLevel = 'Low';
    $riskColor = '#10b981';

    if ($gpa >= 3.70) {
        $predictedHonor = 'First Class Honours (1st)';
    } elseif ($gpa >= 3.30) {
        $predictedHonor = 'Upper Second Class Honours (2:1)';
    } elseif ($gpa >= 2.70) {
        $predictedHonor = 'Lower Second Class Honours (2:2)';
    } elseif ($gpa >= 2.00) {
        $predictedHonor = 'Third Class Honours (3rd)';
    } else {
        $predictedHonor = 'Academic Review Required';
        $riskLevel = 'Critical';
        $riskColor = '#ef4444';
    }

    if ($gpa < 2.50 && $gradeCounts['F'] > 0) {
        $riskLevel = 'High';
        $riskColor = '#f59e0b';
    }

    // 2. Automated Actionable Roadmap Insights
    $insights = [];
    if ($gpa >= 3.70) {
        $insights[] = '🌟 Candidate for Faculty Dean\'s Honours and postgraduate research fellowships.';
        $insights[] = '💡 Recommended to mentor junior cohort students in core technical modules.';
    } elseif ($gpa >= 3.00) {
        $insights[] = '📈 Consistent trajectory. Target 1-2 module grade improvements to transition into First Class standing.';
        $insights[] = '💡 Focus on coursework weightings in advanced capstone modules.';
    } elseif ($gpa >= 2.00) {
        $insights[] = '⚠️ Borderline threshold. Schedule academic advising before final year dissertation.';
        $insights[] = '💡 Revisit prerequisite data structures and analytical foundations.';
    } else {
        $insights[] = '🚨 Immediate Academic Probation Intervention recommended.';
        $insights[] = '💡 Consider module retakes or personal tutoring sessions to recover credit points.';
    }

    if ($gradeCounts['F'] > 0) {
        $insights[] = "❌ Contains {$gradeCounts['F']} failed module(s) requiring remediation prior to graduation eligibility.";
    }

    return [
        'predicted_honor' => $predictedHonor,
        'risk_level'      => $riskLevel,
        'risk_color'      => $riskColor,
        'total_credits'   => $totalCredits,
        'insights'        => $insights,
        'retake_required' => $gradeCounts['F'] > 0
    ];
}