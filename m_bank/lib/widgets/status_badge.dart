import 'package:flutter/material.dart';
import '../core/theme/app_theme.dart';

/// Small pill badge for showing status (e.g. Terverifikasi, Aktif).
class StatusBadge extends StatelessWidget {
  final String text;
  final bool isSuccess;
  final IconData? icon;

  const StatusBadge({
    super.key,
    required this.text,
    this.isSuccess = true,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    final bgColor = isSuccess ? AppColors.successLight : AppColors.warningLight;
    final textColor = isSuccess ? AppColors.success : AppColors.warning;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 14, color: textColor),
            const SizedBox(width: 4),
          ],
          Text(
            text,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: textColor,
            ),
          ),
        ],
      ),
    );
  }
}

