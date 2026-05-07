# Copyright 2026 asdf
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

"""Edge detection node using OpenCV Canny/Sobel/Laplacian."""

import cv2
import numpy as np
import rclpy
from cv_bridge import CvBridge
from rclpy.node import Node
from sensor_msgs.msg import Image
from std_msgs.msg import Int32, String


class EdgeDetectionNode(Node):
    """ROS2 node that captures camera frames and applies edge detection."""

    def __init__(self):
        """Initialize EdgeDetectionNode with parameters, publishers, and camera."""
        super().__init__('edge_detection_node')

        # ROS2 파라미터 선언
        self.declare_parameter('camera_index', 0)
        self.declare_parameter('canny_threshold1', 50)
        self.declare_parameter('canny_threshold2', 150)
        self.declare_parameter('method', 'canny')   # 'canny', 'sobel', 'laplacian'
        self.declare_parameter('blur_ksize', 5)
        self.declare_parameter('display', True)

        cam_idx = self.get_parameter('camera_index').value
        self.thr1 = self.get_parameter('canny_threshold1').value
        self.thr2 = self.get_parameter('canny_threshold2').value
        self.method = self.get_parameter('method').value
        self.blur_k = self.get_parameter('blur_ksize').value
        self.display = self.get_parameter('display').value

        # 카메라 초기화
        self.cap = cv2.VideoCapture(cam_idx)
        if not self.cap.isOpened():
            self.get_logger().error(f'Cannot open /dev/video{cam_idx}')
            raise RuntimeError(f'Camera index {cam_idx} not available')

        self.cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
        self.cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
        w = int(self.cap.get(cv2.CAP_PROP_FRAME_WIDTH))
        h = int(self.cap.get(cv2.CAP_PROP_FRAME_HEIGHT))

        self.bridge = CvBridge()

        # 퍼블리셔
        self.pub_raw = self.create_publisher(Image, 'camera/image_raw', 10)
        self.pub_edge = self.create_publisher(Image, 'edge_detection/image', 10)
        self.pub_combined = self.create_publisher(Image, 'edge_detection/combined', 10)
        self.pub_method = self.create_publisher(String, 'edge_detection/method', 10)
        self.pub_edge_count = self.create_publisher(Int32, 'edge_detection/edge_pixel_count', 10)

        self.frame_count = 0
        self.timer = self.create_timer(1.0 / 30.0, self.timer_callback)

        self.get_logger().info('EdgeDetectionNode started')
        self.get_logger().info(
            f'Camera: /dev/video{cam_idx} | {w}x{h} | Method: {self.method}'
        )
        self.get_logger().info(
            f'Canny thresholds: {self.thr1}/{self.thr2} | Blur ksize: {self.blur_k}'
        )

    def _apply_edge(self, gray):
        """Apply selected edge detection algorithm to grayscale image."""
        blurred = cv2.GaussianBlur(gray, (self.blur_k, self.blur_k), 0)

        if self.method == 'sobel':
            sx = cv2.Sobel(blurred, cv2.CV_64F, 1, 0, ksize=3)
            sy = cv2.Sobel(blurred, cv2.CV_64F, 0, 1, ksize=3)
            edges = np.sqrt(sx ** 2 + sy ** 2)
            edges = np.clip(edges, 0, 255).astype(np.uint8)
        elif self.method == 'laplacian':
            lap = cv2.Laplacian(blurred, cv2.CV_64F)
            edges = np.abs(lap)
            edges = np.clip(edges, 0, 255).astype(np.uint8)
        else:
            edges = cv2.Canny(blurred, self.thr1, self.thr2)

        return edges

    def timer_callback(self):
        """Read camera frame, apply edge detection, publish topics."""
        ret, frame = self.cap.read()
        if not ret:
            self.get_logger().warn('Failed to read camera frame')
            return

        self.frame_count += 1
        h, w = frame.shape[:2]

        # 원본 발행
        raw_msg = self.bridge.cv2_to_imgmsg(frame, encoding='bgr8')
        raw_msg.header.stamp = self.get_clock().now().to_msg()
        raw_msg.header.frame_id = 'camera'
        self.pub_raw.publish(raw_msg)

        # 에지 검출
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        edges = self._apply_edge(gray)

        # 에지를 BGR로 변환 (컬러 오버레이용)
        edges_bgr = cv2.cvtColor(edges, cv2.COLOR_GRAY2BGR)

        # 컬러 에지 오버레이 (초록색)
        edge_colored = frame.copy()
        edge_mask = edges > 0
        edge_colored[edge_mask] = [0, 255, 0]

        # 원본 + 에지 좌우 합성 이미지
        label_orig = frame.copy()
        label_edge = edges_bgr.copy()
        cv2.putText(label_orig, 'Original', (10, 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 255), 2)
        cv2.putText(label_edge, f'Edge ({self.method.upper()})', (10, 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 255), 2)
        combined = np.hstack([label_orig, label_edge])

        # 에지 픽셀 수 계산
        edge_count = int(np.sum(edges > 0))

        # 에지 이미지에 정보 표시
        info_frame = edges_bgr.copy()
        cv2.putText(info_frame, f'Method: {self.method.upper()}', (10, 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)
        cv2.putText(info_frame, f'Edge pixels: {edge_count}', (10, 60),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 0), 1)
        if self.method == 'canny':
            cv2.putText(info_frame, f'Thr: {self.thr1}/{self.thr2}', (10, 85),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 200, 0), 1)

        # 토픽 발행
        stamp = self.get_clock().now().to_msg()

        edge_msg = self.bridge.cv2_to_imgmsg(info_frame, encoding='bgr8')
        edge_msg.header.stamp = stamp
        edge_msg.header.frame_id = 'camera'
        self.pub_edge.publish(edge_msg)

        combined_msg = self.bridge.cv2_to_imgmsg(combined, encoding='bgr8')
        combined_msg.header.stamp = stamp
        combined_msg.header.frame_id = 'camera'
        self.pub_combined.publish(combined_msg)

        method_msg = String()
        method_msg.data = self.method
        self.pub_method.publish(method_msg)

        count_msg = Int32()
        count_msg.data = edge_count
        self.pub_edge_count.publish(count_msg)

        # OpenCV 창 표시
        if self.display:
            cv2.imshow('Original', frame)
            cv2.imshow('Edge Detection', info_frame)
            cv2.imshow('Combined (Original | Edge)', combined)
            key = cv2.waitKey(1) & 0xFF
            if key == ord('q'):
                self.get_logger().info('q key pressed — shutting down')
                rclpy.shutdown()
                return
            elif key == ord('c'):
                self.method = 'canny'
                self.get_logger().info('Switched to Canny')
            elif key == ord('s'):
                self.method = 'sobel'
                self.get_logger().info('Switched to Sobel')
            elif key == ord('l'):
                self.method = 'laplacian'
                self.get_logger().info('Switched to Laplacian')
            elif key == ord('+') or key == ord('='):
                self.thr1 = min(self.thr1 + 10, 500)
                self.thr2 = min(self.thr2 + 10, 500)
                self.get_logger().info(f'Canny thresholds: {self.thr1}/{self.thr2}')
            elif key == ord('-'):
                self.thr1 = max(self.thr1 - 10, 0)
                self.thr2 = max(self.thr2 - 10, 0)
                self.get_logger().info(f'Canny thresholds: {self.thr1}/{self.thr2}')

        if self.frame_count % 90 == 0:
            self.get_logger().info(
                f'Running... frame={self.frame_count} | '
                f'edge_pixels={edge_count} | method={self.method}'
            )

    def destroy_node(self):
        """Release camera and destroy OpenCV windows on shutdown."""
        self.cap.release()
        cv2.destroyAllWindows()
        super().destroy_node()


def main(args=None):
    """Entry point for edge_detection_node."""
    rclpy.init(args=args)
    node = EdgeDetectionNode()
    try:
        rclpy.spin(node)
    except KeyboardInterrupt:
        pass
    finally:
        node.destroy_node()
        rclpy.shutdown()


if __name__ == '__main__':
    main()
