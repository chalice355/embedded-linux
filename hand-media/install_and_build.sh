#!/bin/bash
# shkim_edge 패키지 전체 설치 및 빌드 스크립트
# 실행: sudo bash install_and_build.sh
set -e

WORKSPACE="/home/asdf/ros2_ws"
USER_HOME="/home/asdf"

echo "================================================"
echo " shkim_edge 패키지 설치 및 빌드 스크립트"
echo "================================================"
echo ""

# ──────────────────────────────────────────────────
# [1/7] ROS2 저장소 등록 (없는 경우에만)
# ──────────────────────────────────────────────────
echo "=== [1/7] ROS2 apt 저장소 확인 ==="
if [ ! -f /usr/share/keyrings/ros-archive-keyring.gpg ]; then
    echo "ROS2 GPG 키 등록..."
    apt update && apt install -y curl
    curl -sSL https://raw.githubusercontent.com/ros/rosdistro/master/ros.key \
        -o /usr/share/keyrings/ros-archive-keyring.gpg
fi

if [ ! -f /etc/apt/sources.list.d/ros2.list ]; then
    echo "ROS2 저장소 추가..."
    python3 -c "
with open('/etc/apt/sources.list.d/ros2.list', 'w') as f:
    f.write('deb [arch=amd64 signed-by=/usr/share/keyrings/ros-archive-keyring.gpg] http://packages.ros.org/ros2/ubuntu noble main\n')
"
fi
echo "ROS2 저장소 확인 완료"

# ──────────────────────────────────────────────────
# [2/7] apt 패키지 설치
# ──────────────────────────────────────────────────
echo ""
echo "=== [2/7] apt 업데이트 및 ROS2 Jazzy 설치 ==="
apt update && apt upgrade -y
apt install -y ros-jazzy-desktop ros-dev-tools
apt install -y ros-jazzy-cv-bridge
apt install -y python3-pip python3-numpy

# ──────────────────────────────────────────────────
# [3/7] Python 패키지 설치
# ──────────────────────────────────────────────────
echo ""
echo "=== [3/7] Python 패키지 설치 ==="
pip install "numpy<2.0" --break-system-packages
pip install opencv-python --break-system-packages

# 설치 확인
python3 -c "import cv2; print('OpenCV:', cv2.__version__)"

# ──────────────────────────────────────────────────
# [4/7] .bashrc 환경 설정
# ──────────────────────────────────────────────────
echo ""
echo "=== [4/7] 환경 변수 설정 ==="
BASHRC="$USER_HOME/.bashrc"

if ! grep -q "source /opt/ros/jazzy/setup.bash" "$BASHRC"; then
    echo "source /opt/ros/jazzy/setup.bash" >> "$BASHRC"
    echo ".bashrc에 ROS2 환경 추가 완료"
fi

if ! grep -q "source $WORKSPACE/install/setup.bash" "$BASHRC"; then
    echo "source $WORKSPACE/install/setup.bash" >> "$BASHRC"
    echo ".bashrc에 워크스페이스 환경 추가 완료"
fi

# ──────────────────────────────────────────────────
# [5/7] 워크스페이스 생성 (이미 있으면 스킵)
# ──────────────────────────────────────────────────
echo ""
echo "=== [5/7] 워크스페이스 확인 ==="
mkdir -p "$WORKSPACE/src"
echo "워크스페이스: $WORKSPACE"

# ──────────────────────────────────────────────────
# [6/7] 빌드
# ──────────────────────────────────────────────────
echo ""
echo "=== [6/7] shkim_edge 패키지 빌드 ==="
source /opt/ros/jazzy/setup.bash
cd "$WORKSPACE"
colcon build --packages-select shkim_edge --symlink-install

echo ""
echo "빌드 성공!"

# ──────────────────────────────────────────────────
# [7/7] 완료 안내
# ──────────────────────────────────────────────────
echo ""
echo "=== [7/7] 설치 완료 ==="
echo ""
echo "새 터미널을 열거나 다음 명령어를 실행하세요:"
echo "  source ~/.bashrc"
echo ""
echo "노드 실행:"
echo "  ros2 run shkim_edge edge_detection_node"
echo ""
echo "파라미터 옵션:"
echo "  ros2 run shkim_edge edge_detection_node --ros-args \\"
echo "    -p camera_index:=0 \\"
echo "    -p method:=canny \\"
echo "    -p canny_threshold1:=50 \\"
echo "    -p canny_threshold2:=150"
echo ""
echo "키보드 단축키 (실행 중 창에서):"
echo "  c = Canny 모드"
echo "  s = Sobel 모드"
echo "  l = Laplacian 모드"
echo "  + = 임계값 증가"
echo "  - = 임계값 감소"
echo "  q = 종료"
echo ""
echo "토픽 확인:"
echo "  ros2 topic list"
echo "  ros2 topic hz /camera/image_raw"
echo "  ros2 topic echo /edge_detection/edge_pixel_count"
