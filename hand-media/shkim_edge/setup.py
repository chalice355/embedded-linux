from setuptools import find_packages, setup

package_name = 'shkim_edge'

setup(
    name=package_name,
    version='0.0.0',
    packages=find_packages(exclude=['test']),
    data_files=[
        ('share/ament_index/resource_index/packages',
            ['resource/' + package_name]),
        ('share/' + package_name, ['package.xml']),
    ],
    install_requires=['setuptools'],
    zip_safe=True,
    maintainer='asdf',
    maintainer_email='asdf@todo.todo',
    description='Edge detection using OpenCV with USB camera',
    license='Apache-2.0',
    entry_points={
        'console_scripts': [
            'edge_detection_node = shkim_edge.edge_detection_node:main',
        ],
    },
)
