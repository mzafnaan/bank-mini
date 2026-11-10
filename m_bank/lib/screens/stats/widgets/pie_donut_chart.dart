import 'dart:math' as math;
import 'package:flutter/material.dart';

class ChartSlice {
  final String label;
  final double percentage; // 0 to 100
  final double amount;
  final Color color;

  const ChartSlice({
    required this.label,
    required this.percentage,
    required this.amount,
    required this.color,
  });
}

class PieDonutChart extends StatelessWidget {
  final List<ChartSlice> slices;
  final double size;
  final double holeRadiusRatio; // 0 for full pie, ~0.55 for donut
  final String? centerLabel;
  final String? centerValue;
  final bool isDarkMode;

  const PieDonutChart({
    super.key,
    required this.slices,
    this.size = 220,
    this.holeRadiusRatio = 0.52,
    this.centerLabel,
    this.centerValue,
    this.isDarkMode = false,
  });

  @override
  Widget build(BuildContext context) {
    if (slices.isEmpty) {
      return SizedBox(
        height: size,
        width: size,
        child: Center(
          child: Container(
            width: size * 0.7,
            height: size * 0.7,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: isDarkMode
                    ? const Color(0xFF374151)
                    : const Color(0xFFE5E7EB),
                width: 14,
              ),
            ),
            child: Center(
              child: Text(
                'Belum ada transaksi',
                style: TextStyle(
                  color: isDarkMode
                      ? const Color(0xFF9CA3AF)
                      : const Color(0xFF6B7280),
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ),
        ),
      );
    }

    return SizedBox(
      height: size + 30,
      width: double.infinity,
      child: Stack(
        alignment: Alignment.center,
        children: [
          CustomPaint(
            size: Size(size + 60, size + 20),
            painter: _PieChartPainter(
              slices: slices,
              holeRadiusRatio: holeRadiusRatio,
              separatorColor: isDarkMode ? const Color(0xFF1E222D) : Colors.white,
            ),
          ),
          if (centerLabel != null || centerValue != null)
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (centerLabel != null)
                  Text(
                    centerLabel!,
                    style: TextStyle(
                      color: isDarkMode
                          ? const Color(0xFF9CA3AF)
                          : const Color(0xFF6B7280),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                if (centerValue != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    centerValue!,
                    style: TextStyle(
                      color: isDarkMode ? Colors.white : const Color(0xFF111827),
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ],
            ),
        ],
      ),
    );
  }
}

class _PieChartPainter extends CustomPainter {
  final List<ChartSlice> slices;
  final double holeRadiusRatio;
  final Color separatorColor;

  _PieChartPainter({
    required this.slices,
    required this.holeRadiusRatio,
    required this.separatorColor,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = math.min(size.width / 2 - 40, size.height / 2 - 10);
    final holeRadius = radius * holeRadiusRatio;

    final rect = Rect.fromCircle(center: center, radius: radius);

    double startAngle = -math.pi / 2;

    for (final slice in slices) {
      if (slice.percentage <= 0) continue;
      final sweepAngle = (slice.percentage / 100) * 2 * math.pi;

      // Draw slice
      final paint = Paint()
        ..color = slice.color
        ..style = PaintingStyle.fill;

      if (holeRadiusRatio > 0) {
        final path = Path();
        path.arcTo(rect, startAngle, sweepAngle, false);
        path.arcTo(
          Rect.fromCircle(center: center, radius: holeRadius),
          startAngle + sweepAngle,
          -sweepAngle,
          false,
        );
        path.close();
        canvas.drawPath(path, paint);
      } else {
        canvas.drawArc(rect, startAngle, sweepAngle, true, paint);
      }

      // Draw separator border between slices
      final strokePaint = Paint()
        ..color = separatorColor
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2.5;

      if (slices.length > 1) {
        if (holeRadiusRatio > 0) {
          final path = Path();
          path.arcTo(rect, startAngle, sweepAngle, false);
          path.arcTo(
            Rect.fromCircle(center: center, radius: holeRadius),
            startAngle + sweepAngle,
            -sweepAngle,
            false,
          );
          path.close();
          canvas.drawPath(path, strokePaint);
        } else {
          canvas.drawArc(rect, startAngle, sweepAngle, true, strokePaint);
        }
      }

      // Calculate label position on outer edge
      final middleAngle = startAngle + sweepAngle / 2;
      final labelRadius = radius + 14;
      final lineEnd = Offset(
        center.dx + labelRadius * math.cos(middleAngle),
        center.dy + labelRadius * math.sin(middleAngle),
      );

      // Draw slice percentage text callout
      final isRight = math.cos(middleAngle) >= 0;
      final textSpan = TextSpan(
        text: '${slice.percentage.toStringAsFixed(1)}%',
        style: TextStyle(
          color: slice.color,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      );
      final textPainter = TextPainter(
        text: textSpan,
        textDirection: TextDirection.ltr,
      )..layout();

      final textOffset = Offset(
        isRight ? lineEnd.dx + 4 : lineEnd.dx - textPainter.width - 4,
        lineEnd.dy - textPainter.height / 2,
      );

      // Only draw text if it fits
      if (slice.percentage >= 5) {
        textPainter.paint(canvas, textOffset);
      }

      startAngle += sweepAngle;
    }
  }

  @override
  bool shouldRepaint(covariant _PieChartPainter oldDelegate) {
    return oldDelegate.slices != slices ||
        oldDelegate.holeRadiusRatio != holeRadiusRatio ||
        oldDelegate.separatorColor != separatorColor;
  }
}
